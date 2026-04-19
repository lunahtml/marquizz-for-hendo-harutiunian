<?php
//src/Admin/API/Controllers/RestQuestionController.php
declare(strict_types=1);

namespace SurveySphere\Admin\API\Controllers;

use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use SurveySphere\Database\Repositories\QuestionRepository;
use SurveySphere\Database\Repositories\OptionRepository;
use SurveySphere\Database\Repositories\SurveyRepository;

final class RestQuestionController extends WP_REST_Controller
{
    public function __construct()
    {
        $this->namespace = 'survey-sphere/v1';
        $this->rest_base = 'questions';
    }
    
    public function register_routes(): void
    {
        // GET /questions
        register_rest_route($this->namespace, '/' . $this->rest_base, [
            [
                'methods' => 'GET',
                'callback' => [$this, 'get_items'],
                'permission_callback' => [$this, 'get_items_permissions_check'],
                'args' => [
                    'exclude_survey_id' => [
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
        
        // PUT /questions/{id}
        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<id>[\w-]+)', [
            [
                'methods' => 'PUT',
                'callback' => [$this, 'update_item'],
                'permission_callback' => [$this, 'update_item_permissions_check'],
            ],
        ]);
    }
    
    public function get_items_permissions_check($request): bool
    {
        return true;
    }
    
    public function get_items($request): WP_REST_Response|WP_Error
    {
        $excludeSurveyPublicId = $request->get_param('exclude_survey_id');
        
        $excludeSurveyId = null;
        if ($excludeSurveyPublicId) {
            $surveyRepo = new SurveyRepository();
            $survey = $surveyRepo->findByPublicId($excludeSurveyPublicId);
            if ($survey) {
                $excludeSurveyId = $survey->id;
            }
        }
        
        $questionRepo = new QuestionRepository();
        $questions = $questionRepo->findAll($excludeSurveyId);
        
        $optionRepo = new OptionRepository();
        $data = [];
        
        foreach ($questions as $question) {
            $options = $optionRepo->findByQuestionId($question->id);
            $data[] = [
                'id' => $question->publicId,
                'text' => $question->text,
                'options' => array_map(function($opt) {
                    return [
                        'id' => $opt->publicId,
                        'text' => $opt->text,
                        'score' => $opt->score,
                    ];
                }, $options),
            ];
        }
        
        return new WP_REST_Response(['questions' => $data], 200);
    }
    
    public function create_item_permissions_check($request): bool
    {
        return current_user_can('manage_survey_sphere');
    }
    
    public function create_item($request): WP_REST_Response|WP_Error
    {
        $text = sanitize_textarea_field($request->get_param('text') ?? '');
        
        if (empty($text)) {
            return new WP_Error('missing_text', 'Question text is required', ['status' => 400]);
        }
        
        $questionRepo = new QuestionRepository();
        $question = $questionRepo->create(['text' => $text]);
        
        if (!$question) {
            return new WP_Error('create_failed', 'Failed to create question', ['status' => 500]);
        }
        
        return new WP_REST_Response([
            'message' => 'Question created',
            'question' => [
                'id' => $question->publicId,
                'text' => $question->text,
                'options' => []
            ]
        ], 201);
    }

    public function update_item_permissions_check($request): bool
    {
        return current_user_can('manage_survey_sphere');
    }

    public function update_item($request): WP_REST_Response|WP_Error
    {
        $questionPublicId = $request->get_param('id');
        $text = $request->get_param('text');
        
        $questionRepo = new QuestionRepository();
        $question = $questionRepo->findByPublicId($questionPublicId);
        
        if (!$question) {
            return new WP_Error('question_not_found', 'Question not found', ['status' => 404]);
        }
        
        if ($text !== null) {
            $questionRepo->update($question->id, ['text' => sanitize_textarea_field($text)]);
        }
        
        return new WP_REST_Response(['message' => 'Question updated'], 200);
    }
}