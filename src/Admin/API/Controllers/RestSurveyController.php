<?php
//src/Admin/API/Controllers/RestSurveyController.php
declare(strict_types=1);

namespace SurveySphere\Admin\API\Controllers;

use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use SurveySphere\Database\Repositories\SurveyRepository;

final class RestSurveyController extends WP_REST_Controller
{
    public function __construct()
    {
        $this->namespace = 'survey-sphere/v1';
        $this->rest_base = 'surveys';
    }
    
    public function register_routes(): void
    {
        register_rest_route($this->namespace, '/' . $this->rest_base, [
            [
                'methods' => 'GET',
                'callback' => [$this, 'get_items'],
                'permission_callback' => [$this, 'get_items_permissions_check'],
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
        ]);
    }
    
    public function get_items_permissions_check($request): bool
    {
        return current_user_can('manage_survey_sphere');
    }
    
    public function get_items($request): WP_REST_Response|WP_Error
    {
        $withStats = $request->get_param('with_stats') === '1';
        
        $surveyRepo = new SurveyRepository();
        $surveys = $surveyRepo->findAll();
        
        $data = array_map(function($survey) use ($withStats, $surveyRepo) {
            $item = [
                'id' => $survey->publicId,
                'name' => $survey->name,
                'description' => $survey->description,
                'chartType' => $survey->chartType,
                'isActive' => $survey->isActive,
                'createdAt' => $survey->createdAt,
            ];
            
            if ($withStats) {
                $stats = $surveyRepo->getStats($survey->id);
                $item['segmentsCount'] = $stats['segments'] ?? 0;
                $item['questionsCount'] = $stats['questions'] ?? 0;
            }
            
            return $item;
        }, $surveys);
        
        return new WP_REST_Response(['surveys' => $data], 200);
    }
    
    public function get_item_permissions_check($request): bool
    {
        return current_user_can('manage_survey_sphere');
    }
    
    public function get_item($request): WP_REST_Response|WP_Error
    {
        $id = $request->get_param('id');
        
        $surveyRepo = new SurveyRepository();
        $survey = $surveyRepo->findByPublicId($id);
        
        if (!$survey) {
            return new WP_Error('not_found', 'Survey not found', ['status' => 404]);
        }
        
        return new WP_REST_Response([
            'id' => $survey->publicId,
            'name' => $survey->name,
            'description' => $survey->description,
            'chartType' => $survey->chartType,
            'isActive' => $survey->isActive,
        ], 200);
    }
    
    public function update_item_permissions_check($request): bool
    {
        return current_user_can('manage_survey_sphere');
    }
    
    public function update_item($request): WP_REST_Response|WP_Error
    {
        $id = $request->get_param('id');
        $chartType = $request->get_param('chart_type');
        
        $surveyRepo = new SurveyRepository();
        $survey = $surveyRepo->findByPublicId($id);
        
        if (!$survey) {
            return new WP_Error('not_found', 'Survey not found', ['status' => 404]);
        }
        
        if ($chartType) {
            $surveyRepo->update($survey->id, ['chart_type' => $chartType]);
        }
        
        return new WP_REST_Response(['message' => 'Survey updated'], 200);
    }
}