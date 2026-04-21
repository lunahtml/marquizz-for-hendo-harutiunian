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
use SurveySphere\Database\Repositories\SurveyQuestionRepository;
use SurveySphere\Database\Repositories\SegmentRepository;

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
                    'with_stats' => [
                        'type' => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'segment_id' => [
                        'type' => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'survey_id' => [
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
        
        // PUT, DELETE /questions/{id}
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
        return true;
    }
    
    public function get_items($request): WP_REST_Response|WP_Error
    {
        $excludeSurveyPublicId = $request->get_param('exclude_survey_id');
        $withStats = $request->get_param('with_stats') === '1';
        $filterSegmentPublicId = $request->get_param('segment_id');
        $filterSurveyPublicId = $request->get_param('survey_id');
        
        // Преобразуем public_id в числовые ID для фильтрации
        $filterSurveyId = null;
        if ($filterSurveyPublicId) {
            $surveyRepo = new SurveyRepository();
            $survey = $surveyRepo->findByPublicId($filterSurveyPublicId);
            $filterSurveyId = $survey ? $survey->id : null;
        }
        
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
        $surveyQuestionRepo = new SurveyQuestionRepository();
        $segmentRepo = new SegmentRepository();
        $surveyRepo = new SurveyRepository();
        
        $data = [];
        
        foreach ($questions as $question) {
            $options = $optionRepo->findByQuestionId($question->id);
            
            $item = [
                'id' => $question->publicId,
                'text' => $question->text,
                'options' => array_map(function($opt) {
                    return [
                        'id' => $opt->publicId,
                        'text' => $opt->text,
                        'score' => $opt->score,
                    ];
                }, $options),
                'optionsCount' => count($options),
            ];
            
            if ($withStats) {
                // Получаем опросы, в которых используется вопрос
                $surveyIds = $surveyQuestionRepo->getSurveyIdsForQuestion($question->id);
                $usedInSurveys = count($surveyIds);
                
                $item['usedInSurveys'] = $usedInSurveys;
                $item['surveyNames'] = [];
                $item['surveyIds'] = $surveyIds;
                
                if ($usedInSurveys > 0) {
                    foreach ($surveyIds as $sid) {
                        $s = $surveyRepo->findById((int) $sid);
                        if ($s) {
                            $item['surveyNames'][] = $s->name;
                        }
                    }
                }
                
                // Получаем сегмент (если вопрос привязан к сегменту в каком-либо опросе)
                $segmentId = null;
                foreach ($surveyIds as $sid) {
                    $seg = $surveyQuestionRepo->getSegmentForQuestion((int) $sid, $question->id);
                    if ($seg) {
                        $segmentId = $seg;
                        break;
                    }
                }
                if ($segmentId) {
                    $segment = $segmentRepo->findById($segmentId);
                    if ($segment) {
                        $item['segmentId'] = $segment->publicId;
                        $item['segmentName'] = $segment->name;
                    }
                }
            }
            
            // Применяем фильтры
            if ($filterSegmentPublicId && (!isset($item['segmentId']) || $item['segmentId'] !== $filterSegmentPublicId)) {
                continue;
            }
            if ($filterSurveyId && (!isset($item['surveyIds']) || !in_array($filterSurveyId, $item['surveyIds']))) {
                continue;
            }
            
            $data[] = $item;
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
        $type = sanitize_text_field($request->get_param('type') ?? 'radio');
        
        if (empty($text)) {
            return new WP_Error('missing_text', 'Question text is required', ['status' => 400]);
        }
        
        $questionRepo = new QuestionRepository();
        $question = $questionRepo->create(['text' => $text, 'type' => $type]);
        
        if (!$question) {
            return new WP_Error('create_failed', 'Failed to create question', ['status' => 500]);
        }
        
        return new WP_REST_Response([
            'message' => 'Question created',
            'question' => [
                'id' => $question->publicId,
                'text' => $question->text,
                'type' => $question->type,
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
        $type = $request->get_param('type');  // ← ДОБАВИТЬ
        
        $questionRepo = new QuestionRepository();
        $question = $questionRepo->findByPublicId($questionPublicId);
        
        if (!$question) {
            return new WP_Error('question_not_found', 'Question not found', ['status' => 404]);
        }
        
        $updateData = [];
        if ($text !== null) {
            $updateData['text'] = sanitize_textarea_field($text);
        }
        if ($type !== null) {
            $updateData['type'] = sanitize_text_field($type);
        }
        
        if (!empty($updateData)) {
            $questionRepo->update($question->id, $updateData);
        }
        
        return new WP_REST_Response(['message' => 'Question updated'], 200);
    }
    
    public function delete_item_permissions_check($request): bool
    {
        return current_user_can('manage_survey_sphere');
    }
    
    public function delete_item($request): WP_REST_Response|WP_Error
    {
        $questionPublicId = $request->get_param('id');
        
        $questionRepo = new QuestionRepository();
        $question = $questionRepo->findByPublicId($questionPublicId);
        
        if (!$question) {
            return new WP_Error('question_not_found', 'Question not found', ['status' => 404]);
        }
        
        $questionRepo->delete($question->id);
        
        return new WP_REST_Response(['message' => 'Question deleted'], 200);
    }
}