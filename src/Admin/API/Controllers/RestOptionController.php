<?php
//src/Admin/API/Controllers/RestOptionController.php
declare(strict_types=1);

namespace SurveySphere\Admin\API\Controllers;

use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use SurveySphere\Database\Repositories\OptionRepository;
use SurveySphere\Database\Repositories\QuestionRepository;

final class RestOptionController extends WP_REST_Controller
{
    public function __construct()
    {
        $this->namespace = 'survey-sphere/v1';
        $this->rest_base = 'options';
    }
    
    public function register_routes(): void
    {
        // POST /questions/{id}/options
        register_rest_route($this->namespace, '/questions/(?P<question_id>[\w-]+)/options', [
            [
                'methods' => 'POST',
                'callback' => [$this, 'create_item'],
                'permission_callback' => [$this, 'create_item_permissions_check'],
            ],
        ]);
        
        // PUT /options/{id}
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
    
    public function create_item_permissions_check($request): bool
    {
        return current_user_can('manage_survey_sphere');
    }
    
    public function create_item($request): WP_REST_Response|WP_Error
    {
        $questionPublicId = $request->get_param('question_id');
        $text = sanitize_text_field($request->get_param('text') ?? '');
        $score = (float) ($request->get_param('score') ?? 0);
        
        if (empty($text)) {
            return new WP_Error('missing_text', 'Option text is required', ['status' => 400]);
        }
        
        $questionRepo = new QuestionRepository();
        $question = $questionRepo->findByPublicId($questionPublicId);
        
        if (!$question) {
            return new WP_Error('question_not_found', 'Question not found', ['status' => 404]);
        }
        
        $optionRepo = new OptionRepository();
        $option = $optionRepo->create([
            'question_id' => $question->id,
            'text' => $text,
            'score' => $score,
        ]);
        
        if (!$option) {
            return new WP_Error('create_failed', 'Failed to create option', ['status' => 500]);
        }
        
        return new WP_REST_Response([
            'message' => 'Option created',
            'option' => [
                'id' => $option->publicId,
                'text' => $option->text,
                'score' => $option->score,
            ]
        ], 201);
    }
    
    public function update_item_permissions_check($request): bool
    {
        return current_user_can('manage_survey_sphere');
    }
    
    public function update_item($request): WP_REST_Response|WP_Error
    {
        $optionPublicId = $request->get_param('id');
        $text = $request->get_param('text');
        $score = $request->get_param('score');
        
        $optionRepo = new OptionRepository();
        $option = $optionRepo->findByPublicId($optionPublicId);
        
        if (!$option) {
            return new WP_Error('option_not_found', 'Option not found', ['status' => 404]);
        }
        
        $updateData = [];
        if ($text !== null) {
            $updateData['text'] = sanitize_text_field($text);
        }
        if ($score !== null) {
            $updateData['score'] = (float) $score;
        }
        
        $optionRepo->update($option->id, $updateData);
        
        return new WP_REST_Response(['message' => 'Option updated'], 200);
    }
    
    public function delete_item_permissions_check($request): bool
    {
        return current_user_can('manage_survey_sphere');
    }
    
    public function delete_item($request): WP_REST_Response|WP_Error
    {
        $optionPublicId = $request->get_param('id');
        
        $optionRepo = new OptionRepository();
        $option = $optionRepo->findByPublicId($optionPublicId);
        
        if (!$option) {
            return new WP_Error('option_not_found', 'Option not found', ['status' => 404]);
        }
        
        $optionRepo->delete($option->id);
        
        return new WP_REST_Response(['message' => 'Option deleted'], 200);
    }
}