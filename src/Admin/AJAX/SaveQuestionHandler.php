<?php
//src/Admin/AJAX/SaveQuestionHandler.php
declare(strict_types=1);

namespace SurveySphere\Admin\AJAX;

use SurveySphere\Database\Repositories\QuestionRepository;
use SurveySphere\Database\Repositories\SurveyRepository;
use SurveySphere\Security\NonceManager;

final class SaveQuestionHandler
{
    public static function handle(): void
    {
        // Verify nonce
        if (!NonceManager::verify('survey_sphere_admin', $_POST['_wpnonce'] ?? '')) {
            wp_send_json_error(['message' => 'Security check failed'], 403);
        }
        
        // Check permissions
        if (!current_user_can('manage_survey_sphere')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }
        
        $surveyPublicId = sanitize_text_field($_POST['survey_id'] ?? '');
        $questionText = sanitize_textarea_field($_POST['question_text'] ?? '');
        $orderIndex = (int) ($_POST['order_index'] ?? 0);
        
        if (empty($surveyPublicId) || empty($questionText)) {
            wp_send_json_error(['message' => 'Survey ID and question text are required'], 400);
        }
        
        try {
            $surveyRepo = new SurveyRepository();
            $survey = $surveyRepo->findByPublicId($surveyPublicId);
            
            if (!$survey) {
                wp_send_json_error(['message' => 'Survey not found'], 404);
            }
            
            $questionRepo = new QuestionRepository();
            $question = $questionRepo->create([
                'survey_id' => $survey->id,
                'text' => $questionText,
                'order_index' => $orderIndex,
            ]);
            
            wp_send_json_success([
                'message' => 'Question saved successfully',
                'question' => [
                    'id' => $question->publicId,
                    'text' => $question->text,
                    'order_index' => $question->orderIndex,
                ]
            ]);
            
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()], 500);
        }
    }
}