<?php
//src/Admin/AJAX/SaveOptionHandler.php
declare(strict_types=1);

namespace SurveySphere\Admin\AJAX;

use SurveySphere\Database\Repositories\OptionRepository;
use SurveySphere\Database\Repositories\QuestionRepository;
use SurveySphere\Security\NonceManager;

final class SaveOptionHandler
{
    public static function handle(): void
    {
        if (!NonceManager::verify('survey_sphere_admin', $_POST['_wpnonce'] ?? '')) {
            wp_send_json_error(['message' => 'Security check failed'], 403);
        }
        
        if (!current_user_can('manage_survey_sphere')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }
        
        $questionPublicId = sanitize_text_field($_POST['question_id'] ?? '');
        $optionText = sanitize_text_field($_POST['option_text'] ?? '');
        $score = (float) ($_POST['score'] ?? 0);
        $orderIndex = (int) ($_POST['order_index'] ?? 0);
        
        if (empty($questionPublicId) || empty($optionText)) {
            wp_send_json_error(['message' => 'Question ID and option text are required'], 400);
        }
        
        try {
            $questionRepo = new QuestionRepository();
            $question = $questionRepo->findByPublicId($questionPublicId);
            
            if (!$question) {
                wp_send_json_error(['message' => 'Question not found'], 404);
            }
            
            $optionRepo = new OptionRepository();
            $option = $optionRepo->create([
                'question_id' => $question->id,
                'text' => $optionText,
                'score' => $score,
                'order_index' => $orderIndex,
            ]);
            
            wp_send_json_success([
                'message' => 'Option saved successfully',
                'option' => [
                    'id' => $option->publicId,
                    'text' => $option->text,
                    'score' => $option->score,
                ]
            ]);
            
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()], 500);
        }
    }
}