<?php
//src/Admin/AJAX/GetQuestionsHandler.php
declare(strict_types=1);

namespace SurveySphere\Admin\AJAX;

use SurveySphere\Database\Repositories\QuestionRepository;
use SurveySphere\Database\Repositories\OptionRepository;
use SurveySphere\Security\NonceManager;

final class GetQuestionsHandler
{
    public static function handle(): void
    {
        if (!NonceManager::verify('survey_sphere_admin', $_GET['_wpnonce'] ?? '')) {
            wp_send_json_error(['message' => 'Security check failed'], 403);
        }
        
        if (!current_user_can('manage_survey_sphere')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }
        
        $excludeSurveyPublicId = sanitize_text_field($_GET['exclude_survey_id'] ?? '');
        
        try {
            $questionRepo = new QuestionRepository();
            
            // Если передан public_id опроса, получаем его числовой ID
            $excludeSurveyId = null;
            if (!empty($excludeSurveyPublicId)) {
                $surveyRepo = new \SurveySphere\Database\Repositories\SurveyRepository();
                $survey = $surveyRepo->findByPublicId($excludeSurveyPublicId);
                if ($survey) {
                    $excludeSurveyId = $survey->id;
                }
            }
            
            $questions = $questionRepo->findAll($excludeSurveyId);
            
            $optionRepo = new OptionRepository();
            $result = [];
            
            foreach ($questions as $question) {
                $options = $optionRepo->findByQuestionId($question->id);
                $result[] = [
                    'id' => $question->publicId,
                    'text' => $question->text,
                    'options_count' => count($options),
                ];
            }
            
            wp_send_json_success(['questions' => $result]);
            
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()], 500);
        }
    }
}