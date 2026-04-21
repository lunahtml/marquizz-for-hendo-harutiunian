<?php
//src/Admin/API/Controllers/RestRecommendationController.php
declare(strict_types=1);

namespace SurveySphere\Admin\API\Controllers;

use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use SurveySphere\Database\Repositories\RecommendationRepository;
use SurveySphere\Database\Repositories\SurveyRepository;
use SurveySphere\Database\Repositories\SegmentRepository;
use SurveySphere\Database\Models\Recommendation;

final class RestRecommendationController extends WP_REST_Controller
{
    public function __construct()
    {
        $this->namespace = 'survey-sphere/v1';
        $this->rest_base = 'recommendations';
    }
    
    public function register_routes(): void
    {
        register_rest_route($this->namespace, '/' . $this->rest_base, [
            [
                'methods' => 'GET',
                'callback' => [$this, 'get_items'],
                'permission_callback' => [$this, 'get_items_permissions_check'],
                'args' => [
                    'survey_id' => [
                        'required' => true,
                        'type' => 'string',
                    ],
                    'segment_id' => [
                        'type' => 'string',
                    ],
                ],
            ],
            [
                'methods' => 'POST',
                'callback' => [$this, 'create_item'],
                'permission_callback' => [$this, 'create_item_permissions_check'],
            ],
        ]);
        
        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<id>[\w-]+)', [
            [
                'methods' => 'PUT',
                'callback' => [$this, 'update_item'],
                'permission_callback' => [$this, 'update_item_permissions_check'],
            ],
            [
                'methods' => 'DELETE',
                'callback' => [$this, 'delete_item'],
                'permission_callback' => [$this, 'delete_item_permissions_check'],
            ],
        ]);
    }
    
    public function get_items_permissions_check($request): bool
    {
        return current_user_can('manage_survey_sphere') || true;
    }
    
    public function get_items($request): WP_REST_Response|WP_Error
    {
        $surveyPublicId = $request->get_param('survey_id');
        $segmentPublicId = $request->get_param('segment_id');
        
        $surveyRepo = new SurveyRepository();
        $survey = $surveyRepo->findByPublicId($surveyPublicId);
        
        if (!$survey) {
            return new WP_Error('survey_not_found', 'Survey not found', ['status' => 404]);
        }
        
        if ($segmentPublicId === null) {
            // Получаем все рекомендации для опроса
            global $wpdb;
            $table = $wpdb->prefix . 'survey_sphere_recommendations';
            $rows = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM {$table} WHERE survey_id = %d AND is_active = 1 ORDER BY order_index ASC, min_score ASC",
                $survey->id
            ), ARRAY_A);
            
            $recommendations = array_map([Recommendation::class, 'fromArray'], $rows ?: []);
        } else {
            $segmentId = null;
            if ($segmentPublicId) {
                $segmentRepo = new SegmentRepository();
                $segment = $segmentRepo->findByPublicId($segmentPublicId);
                $segmentId = $segment ? $segment->id : null;
            }
            
            $repo = new RecommendationRepository();
            $recommendations = $repo->findBySurveyId($survey->id, $segmentId);
        }
        
        $data = array_map(function($rec) {
            $segmentPublicId = null;
            if ($rec->segmentId) {
                $segmentRepo = new SegmentRepository();
                $segment = $segmentRepo->findById($rec->segmentId);
                $segmentPublicId = $segment ? $segment->publicId : null;
            }
            
            return [
                'id' => $rec->publicId,
                'min_score' => $rec->minScore,
                'max_score' => $rec->maxScore,
                'title' => $rec->title,
                'description' => $rec->description,
                'action_text' => $rec->actionText,
                'action_url' => $rec->actionUrl,
                'segment_id' => $segmentPublicId,
                'order_index' => $rec->orderIndex,
            ];
        }, $recommendations);
        
        return new WP_REST_Response(['recommendations' => $data], 200);
    }
    
    
    public function create_item_permissions_check($request): bool
    {
        return current_user_can('manage_survey_sphere');
    }
    
    public function create_item($request): WP_REST_Response|WP_Error
    {
        $surveyPublicId = $request->get_param('survey_id');
        $segmentPublicId = $request->get_param('segment_id');
        $title = $request->get_param('title');
        
        if (empty($title)) {
            return new WP_Error('missing_title', 'Title is required', ['status' => 400]);
        }
        
        $surveyRepo = new SurveyRepository();
        $survey = $surveyRepo->findByPublicId($surveyPublicId);
        if (!$survey) {
            return new WP_Error('survey_not_found', 'Survey not found', ['status' => 404]);
        }
        
        $segmentId = null;
        if ($segmentPublicId && $segmentPublicId !== '0' && $segmentPublicId !== 'null') {
            $segmentRepo = new SegmentRepository();
            $segment = $segmentRepo->findByPublicId($segmentPublicId);
            $segmentId = $segment ? $segment->id : null;
        }
        
        $repo = new RecommendationRepository();
        $rec = $repo->create([
            'survey_id' => $survey->id,
            'segment_id' => $segmentId,
            'min_score' => (int)($request->get_param('min_score') ?? 0),
            'max_score' => (int)($request->get_param('max_score') ?? 100),
            'title' => $title,
            'description' => $request->get_param('description'),
            'action_text' => $request->get_param('action_text'),
            'action_url' => $request->get_param('action_url'),
            'order_index' => (int)($request->get_param('order_index') ?? 0),
        ]);
        
        return new WP_REST_Response([
            'message' => 'Recommendation created',
            'recommendation' => [
                'id' => $rec->publicId,
                'title' => $rec->title,
                'segment_id' => $rec->segmentId,
            ]
        ], 201);
    }
    
    public function update_item_permissions_check($request): bool
    {
        return current_user_can('manage_survey_sphere');
    }
    
    public function update_item($request): WP_REST_Response|WP_Error
    {
        $id = $request->get_param('id');
        $repo = new RecommendationRepository();
        $rec = $repo->findByPublicId($id);
        
        if (!$rec) {
            return new WP_Error('not_found', 'Recommendation not found', ['status' => 404]);
        }
        
        $updateData = [];
        $fields = ['min_score', 'max_score', 'title', 'description', 'action_text', 'action_url', 'order_index', 'segment_id'];
        
        foreach ($fields as $field) {
            if ($request->has_param($field)) {
                $value = $request->get_param($field);
                
                if ($field === 'segment_id') {
                    if (empty($value) || $value === 0 || $value === '0' || $value === '') {
                        $updateData[$field] = null;
                    } else {
                        // Это публичный ID, нужно найти числовой ID
                        $segmentRepo = new SegmentRepository();
                        $segment = $segmentRepo->findByPublicId((string) $value);
                        $updateData[$field] = $segment ? $segment->id : null;
                    }
                } else {
                    $updateData[$field] = $value;
                }
            }
        }
        
        $repo->update($rec->id, $updateData);
        
        return new WP_REST_Response(['message' => 'Recommendation updated'], 200);
    }
    
    public function delete_item_permissions_check($request): bool
    {
        return current_user_can('manage_survey_sphere');
    }
    
    public function delete_item($request): WP_REST_Response|WP_Error
    {
        $id = $request->get_param('id');
        $repo = new RecommendationRepository();
        $rec = $repo->findByPublicId($id);
        
        if (!$rec) {
            return new WP_Error('not_found', 'Recommendation not found', ['status' => 404]);
        }
        
        $repo->delete($rec->id);
        
        return new WP_REST_Response(['message' => 'Recommendation deleted'], 200);
    }
}