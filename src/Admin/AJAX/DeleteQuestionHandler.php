<?php
//src/Admin/AJAX/DeleteQuestionHandler.php
declare(strict_types=1);

namespace SurveySphere\Admin\AJAX;

use SurveySphere\Database\Repositories\QuestionRepository;
use SurveySphere\Security\NonceManager;
use SurveySphere\Exceptions\DatabaseException;

final class DeleteQuestionHandler
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
        
        if (empty($questionPublicId)) {
            wp_send_json_error(['message' => 'Question ID is required'], 400);
        }
        
        try {
            $questionRepo = new QuestionRepository();
            $question = $questionRepo->findByPublicId($questionPublicId);
            
            if (!$question) {
                wp_send_json_error(['message' => 'Question not found'], 404);
            }
            
            $deleted = $questionRepo->delete($question->id);
            
            if ($deleted) {
                wp_send_json_success(['message' => 'Question deleted successfully']);
            } else {
                throw new DatabaseException('Failed to delete question');
            }
            
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()], 500);
        }
    }
}