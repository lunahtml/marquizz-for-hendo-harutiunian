<?php
//src/Admin/AJAX/AttachQuestionHandler.php
declare(strict_types=1);

namespace SurveySphere\Admin\AJAX;

use SurveySphere\Database\Repositories\SurveyRepository;
use SurveySphere\Database\Repositories\QuestionRepository;
use SurveySphere\Database\Repositories\SurveyQuestionRepository;
use SurveySphere\Security\NonceManager;

final class AttachQuestionHandler
{
    public static function handle(): void
    {
        if (!NonceManager::verify('survey_sphere_admin', $_POST['_wpnonce'] ?? '')) {
            wp_send_json_error(['message' => 'Security check failed'], 403);
        }
        
        if (!current_user_can('manage_survey_sphere')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }
        
        $surveyPublicId = sanitize_text_field($_POST['survey_id'] ?? '');
        $questionPublicId = sanitize_text_field($_POST['question_id'] ?? '');
        $orderIndex = (int) ($_POST['order_index'] ?? 0);
        
        if (empty($surveyPublicId) || empty($questionPublicId)) {
            wp_send_json_error(['message' => 'Survey ID and Question ID are required'], 400);
        }
        
        try {
            $surveyRepo = new SurveyRepository();
            $survey = $surveyRepo->findByPublicId($surveyPublicId);
            
            if (!$survey) {
                wp_send_json_error(['message' => 'Survey not found'], 404);
            }
            
            $questionRepo = new QuestionRepository();
            $question = $questionRepo->findByPublicId($questionPublicId);
            
            if (!$question) {
                wp_send_json_error(['message' => 'Question not found'], 404);
            }
            
            $surveyQuestionRepo = new SurveyQuestionRepository();
            
            // Проверяем, не привязан ли уже вопрос к этому опросу
            $existingQuestions = $surveyQuestionRepo->getQuestionIdsForSurvey($survey->id);
            if (in_array($question->id, $existingQuestions)) {
                wp_send_json_error(['message' => 'Question already attached to this survey'], 400);
            }
            
            // Привязываем вопрос к опросу
            $surveyQuestionRepo->attachQuestion($survey->id, $question->id, $orderIndex);
            
            wp_send_json_success([
                'message' => 'Question attached successfully',
                'question' => [
                    'id' => $question->publicId,
                    'text' => $question->text,
                ]
            ]);
            
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()], 500);
        }
    }
}