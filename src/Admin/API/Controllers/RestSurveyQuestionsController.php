<?php
//src/Admin/API/Controllers/RestSurveyQuestionsController.php
declare(strict_types=1);

namespace SurveySphere\Admin\API\Controllers;

use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use SurveySphere\Database\Repositories\SurveyRepository;
use SurveySphere\Database\Repositories\QuestionRepository;
use SurveySphere\Database\Repositories\SurveyQuestionRepository;
use SurveySphere\Database\Repositories\OptionRepository;

final class RestSurveyQuestionsController extends WP_REST_Controller
{
    public function __construct()
    {
        $this->namespace = 'survey-sphere/v1';
        $this->rest_base = 'surveys/(?P<survey_id>[\w-]+)/questions';
    }
    
    public function register_routes(): void
    {
        register_rest_route($this->namespace, '/' . $this->rest_base, [
            [
                'methods' => 'GET',
                'callback' => [$this, 'get_items'],
                'permission_callback' => [$this, 'get_items_permissions_check'],
            ],
            [
                'methods' => 'POST',
                'callback' => [$this, 'attach_question'],
                'permission_callback' => [$this, 'create_item_permissions_check'],
            ],
        ]);
        
        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<question_id>[\w-]+)', [
            [
                'methods' => 'PUT',
                'callback' => [$this, 'update_question'],
                'permission_callback' => [$this, 'update_item_permissions_check'],
            ],
            [
                'methods' => 'DELETE',
                'callback' => [$this, 'detach_question'],
                'permission_callback' => [$this, 'delete_item_permissions_check'],
            ],
        ]);
    }
    
    public function get_items_permissions_check($request): bool
    {
        return true;
    }
    
    public function get_items($request): WP_REST_Response|WP_Error
    {
        $surveyPublicId = $request->get_param('survey_id');
        
        $surveyRepo = new SurveyRepository();
        $survey = $surveyRepo->findByPublicId($surveyPublicId);
        
        if (!$survey) {
            return new WP_Error('survey_not_found', 'Survey not found', ['status' => 404]);
        }
        
        $questionRepo = new QuestionRepository();
        $questions = $questionRepo->findBySurveyId($survey->id);
        
        $optionRepo = new OptionRepository();
        $surveyQuestionRepo = new SurveyQuestionRepository();
        $questionsWithSegments = $surveyQuestionRepo->getQuestionsWithSegments($survey->id);
        
        // Получаем все сегменты опроса для маппинга id -> publicId
        $segmentRepo = new \SurveySphere\Database\Repositories\SegmentRepository();
        $allSegments = $segmentRepo->findBySurveyId($survey->id);
        $segmentIdToPublicId = [];
        foreach ($allSegments as $seg) {
            $segmentIdToPublicId[$seg->id] = $seg->publicId;
        }
        
        // Строим карту: question_id -> segment_public_id
        $segmentMap = [];
        foreach ($questionsWithSegments as $sq) {
            $numericSegmentId = $sq['segment_id'];
            $segmentMap[$sq['question_id']] = $numericSegmentId 
                ? ($segmentIdToPublicId[$numericSegmentId] ?? null) 
                : null;
        }
        
        $data = [];
        foreach ($questions as $question) {
            $options = $optionRepo->findByQuestionId($question->id);
            $data[] = [
                'id' => $question->publicId,
                'text' => $question->text,
                'segmentId' => $segmentMap[$question->id] ?? null,
                'options' => array_map(function($opt) {
                    return ['id' => $opt->publicId, 'text' => $opt->text, 'score' => $opt->score];
                }, $options),
            ];
        }
        
        return new WP_REST_Response(['questions' => $data], 200);
    }
    
    public function create_item_permissions_check($request): bool
    {
        return current_user_can('manage_survey_sphere');
    }
    
    public function attach_question($request): WP_REST_Response|WP_Error
    {
        $surveyPublicId = $request->get_param('survey_id');
        $questionPublicId = sanitize_text_field($request->get_param('question_id') ?? '');
        $segmentId = $request->get_param('segment_id');
        
        $surveyRepo = new SurveyRepository();
        $survey = $surveyRepo->findByPublicId($surveyPublicId);
        
        if (!$survey) {
            return new WP_Error('survey_not_found', 'Survey not found', ['status' => 404]);
        }
        
        $questionRepo = new QuestionRepository();
        $question = $questionRepo->findByPublicId($questionPublicId);
        
        if (!$question) {
            return new WP_Error('question_not_found', 'Question not found', ['status' => 404]);
        }
        
        $surveyQuestionRepo = new SurveyQuestionRepository();
        $surveyQuestionRepo->attachQuestion($survey->id, $question->id, 0, $segmentId);
        
        return new WP_REST_Response(['message' => 'Question attached'], 200);
    }
    
    public function update_item_permissions_check($request): bool
    {
        return current_user_can('manage_survey_sphere');
    }
    
    public function update_question($request): WP_REST_Response|WP_Error
    {
        $surveyPublicId = $request->get_param('survey_id');
        $questionPublicId = $request->get_param('question_id');
        $segmentPublicId = $request->get_param('segment_id');  // ← это public_id, а не числовой id
        
        $surveyRepo = new SurveyRepository();
        $survey = $surveyRepo->findByPublicId($surveyPublicId);
        
        if (!$survey) {
            return new WP_Error('survey_not_found', 'Survey not found', ['status' => 404]);
        }
        
        $questionRepo = new QuestionRepository();
        $question = $questionRepo->findByPublicId($questionPublicId);
        
        if (!$question) {
            return new WP_Error('question_not_found', 'Question not found', ['status' => 404]);
        }
        
        // Находим числовой ID сегмента по public_id
        $segmentId = null;
        if ($segmentPublicId && $segmentPublicId !== 'null' && $segmentPublicId !== '') {
            $segmentRepo = new \SurveySphere\Database\Repositories\SegmentRepository();
            $segment = $segmentRepo->findByPublicId($segmentPublicId);
            if ($segment) {
                $segmentId = $segment->id;
            }
        }
        
        $surveyQuestionRepo = new SurveyQuestionRepository();
        $surveyQuestionRepo->updateSegment($survey->id, $question->id, $segmentId);
        
        return new WP_REST_Response(['message' => 'Question updated'], 200);
    }
    
    public function delete_item_permissions_check($request): bool
    {
        return current_user_can('manage_survey_sphere');
    }
    
    public function detach_question($request): WP_REST_Response|WP_Error
    {
        $surveyPublicId = $request->get_param('survey_id');
        $questionPublicId = $request->get_param('question_id');
        
        $surveyRepo = new SurveyRepository();
        $survey = $surveyRepo->findByPublicId($surveyPublicId);
        
        if (!$survey) {
            return new WP_Error('survey_not_found', 'Survey not found', ['status' => 404]);
        }
        
        $questionRepo = new QuestionRepository();
        $question = $questionRepo->findByPublicId($questionPublicId);
        
        if (!$question) {
            return new WP_Error('question_not_found', 'Question not found', ['status' => 404]);
        }
        
        $surveyQuestionRepo = new SurveyQuestionRepository();
        $surveyQuestionRepo->detachQuestion($survey->id, $question->id);
        
        return new WP_REST_Response(['message' => 'Question detached'], 200);
    }
}