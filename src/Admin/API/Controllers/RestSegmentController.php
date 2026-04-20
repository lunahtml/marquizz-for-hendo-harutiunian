<?php
//src/Admin/API/Controllers/RestSegmentController.php
declare(strict_types=1);

namespace SurveySphere\Admin\API\Controllers;

use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use SurveySphere\Database\Repositories\SegmentRepository;
use SurveySphere\Database\Repositories\SurveyRepository;

final class RestSegmentController extends WP_REST_Controller
{
    public function __construct()
    {
        $this->namespace = 'survey-sphere/v1';
        $this->rest_base = 'segments';
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
                        'required' => false,
                        'type' => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
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
                'methods' => 'GET',
                'callback' => [$this, 'get_item'],
                'permission_callback' => [$this, 'get_item_permissions_check'],
            ],
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
        // return true; 
        return current_user_can('manage_survey_sphere');
    }
    
    public function get_items($request): WP_REST_Response|WP_Error
    {
        $surveyPublicId = $request->get_param('survey_id');
        
        // Если survey_id не передан — возвращаем все сегменты
        if (empty($surveyPublicId)) {
            $segmentRepo = new SegmentRepository();
            $allSegments = $segmentRepo->findAll(); // Нужно добавить этот метод
            $data = array_map(function($segment) {
                return [
                    'id' => $segment->publicId,
                    'name' => $segment->name,
                    'color' => $segment->color,
                ];
            }, $allSegments);
            return new WP_REST_Response(['segments' => $data], 200);
        }
        
        // Иначе — сегменты конкретного опроса
        $surveyRepo = new SurveyRepository();
        $survey = $surveyRepo->findByPublicId($surveyPublicId);
        
        if (!$survey) {
            return new WP_Error('survey_not_found', 'Survey not found', ['status' => 404]);
        }
        
        $segmentRepo = new SegmentRepository();
        $segments = $segmentRepo->findBySurveyId($survey->id);
        
        $data = array_map(function($segment) {
            return [
                'id' => $segment->publicId,
                'name' => $segment->name,
                'color' => $segment->color,
            ];
        }, $segments);
        
        return new WP_REST_Response(['segments' => $data], 200);
    }
    
    public function create_item_permissions_check($request): bool
    {
        return current_user_can('manage_survey_sphere');
    }
    
    public function create_item($request): WP_REST_Response|WP_Error
    {
        $surveyPublicId = sanitize_text_field($request->get_param('survey_id'));
        $name = sanitize_text_field($request->get_param('name'));
        
        if (empty($name)) {
            return new WP_Error('missing_name', 'Segment name is required', ['status' => 400]);
        }
        
        $surveyRepo = new SurveyRepository();
        $survey = $surveyRepo->findByPublicId($surveyPublicId);
        
        if (!$survey) {
            return new WP_Error('survey_not_found', 'Survey not found', ['status' => 404]);
        }
        
        $segmentRepo = new SegmentRepository();
        $segment = $segmentRepo->create([
            'survey_id' => $survey->id,
            'name' => $name,
            'description' => sanitize_textarea_field($request->get_param('description') ?? ''),
            'icon' => sanitize_text_field($request->get_param('icon') ?? ''),
            'color' => sanitize_text_field($request->get_param('color') ?? '#36A2EB'),
            'order_index' => (int)($request->get_param('order_index') ?? 0),
        ]);
        
        if (!$segment) {
            return new WP_Error('create_failed', 'Failed to create segment', ['status' => 500]);
        }
        
        return new WP_REST_Response([
            'message' => 'Segment created successfully',
            'segment' => [
                'id' => $segment->publicId,
                'name' => $segment->name,
                'color' => $segment->color,
            ]
        ], 201);
    }
    
    public function get_item_permissions_check($request): bool
    {
        return current_user_can('manage_survey_sphere');
    }
    
    public function get_item($request): WP_REST_Response|WP_Error
    {
        $segmentId = $request->get_param('id');
        
        $segmentRepo = new SegmentRepository();
        $segment = $segmentRepo->findByPublicId($segmentId);
        
        if (!$segment) {
            return new WP_Error('segment_not_found', 'Segment not found', ['status' => 404]);
        }
        
        return new WP_REST_Response([
            'segment' => [
                'id' => $segment->publicId,
                'name' => $segment->name,
                'description' => $segment->description,
                'icon' => $segment->icon,
                'color' => $segment->color,
                'order_index' => $segment->orderIndex,
            ]
        ], 200);
    }
    
    public function update_item_permissions_check($request): bool
    {
        return current_user_can('manage_survey_sphere');
    }
    
    public function update_item($request): WP_REST_Response|WP_Error
    {
        $segmentId = $request->get_param('id');
        
        $segmentRepo = new SegmentRepository();
        $segment = $segmentRepo->findByPublicId($segmentId);
        
        if (!$segment) {
            return new WP_Error('segment_not_found', 'Segment not found', ['status' => 404]);
        }
        
        $updateData = [];
        if ($request->has_param('name')) {
            $updateData['name'] = sanitize_text_field($request->get_param('name'));
        }
        if ($request->has_param('description')) {
            $updateData['description'] = sanitize_textarea_field($request->get_param('description'));
        }
        if ($request->has_param('icon')) {
            $updateData['icon'] = sanitize_text_field($request->get_param('icon'));
        }
        if ($request->has_param('color')) {
            $updateData['color'] = sanitize_text_field($request->get_param('color'));
        }
        if ($request->has_param('order_index')) {
            $updateData['order_index'] = (int)$request->get_param('order_index');
        }
        
        $updated = $segmentRepo->update($segment->id, $updateData);
        
        if (!$updated) {
            return new WP_Error('update_failed', 'Failed to update segment', ['status' => 500]);
        }
        
        return new WP_REST_Response(['message' => 'Segment updated successfully'], 200);
    }
    
    public function delete_item_permissions_check($request): bool
    {
        return current_user_can('manage_survey_sphere');
    }
    
    public function delete_item($request): WP_REST_Response|WP_Error
    {
        $segmentId = $request->get_param('id');
        
        $segmentRepo = new SegmentRepository();
        $segment = $segmentRepo->findByPublicId($segmentId);
        
        if (!$segment) {
            return new WP_Error('segment_not_found', 'Segment not found', ['status' => 404]);
        }
        
        $deleted = $segmentRepo->delete($segment->id);
        
        if (!$deleted) {
            return new WP_Error('delete_failed', 'Failed to delete segment', ['status' => 500]);
        }
        
        return new WP_REST_Response(['message' => 'Segment deleted successfully'], 200);
    }
}