<?php
declare(strict_types=1);
//src/Admin/API/Controllers/RestSurveyController.php

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
    }
    
    public function get_items_permissions_check($request): bool
    {
        return current_user_can('manage_survey_sphere');
    }
    
    public function get_items($request): WP_REST_Response|WP_Error
    {
        $surveyRepo = new SurveyRepository();
        $surveys = $surveyRepo->findAll();
        
        $data = array_map(function($survey) {
            return [
                'id' => $survey->publicId,
                'name' => $survey->name,
                'description' => $survey->description,
                'chartType' => $survey->chartType,
                'isActive' => $survey->isActive,
                'createdAt' => $survey->createdAt,
            ];
        }, $surveys);
        
        return new WP_REST_Response(['surveys' => $data], 200);
    }
}