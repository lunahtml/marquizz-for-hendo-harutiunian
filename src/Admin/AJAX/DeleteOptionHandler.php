<?php
//src/Admin/AJAX/DeleteOptionHandler.php
declare(strict_types=1);

namespace SurveySphere\Admin\AJAX;

use SurveySphere\Database\Repositories\OptionRepository;
use SurveySphere\Security\NonceManager;
use SurveySphere\Exceptions\DatabaseException;

final class DeleteOptionHandler
{
    public static function handle(): void
    {
        if (!NonceManager::verify('survey_sphere_admin', $_POST['_wpnonce'] ?? '')) {
            wp_send_json_error(['message' => 'Security check failed'], 403);
        }
        
        if (!current_user_can('manage_survey_sphere')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }
        
        $optionPublicId = sanitize_text_field($_POST['option_id'] ?? '');
        
        if (empty($optionPublicId)) {
            wp_send_json_error(['message' => 'Option ID is required'], 400);
        }
        
        try {
            $optionRepo = new OptionRepository();
            $option = $optionRepo->findByPublicId($optionPublicId);
            
            if (!$option) {
                wp_send_json_error(['message' => 'Option not found'], 404);
            }
            
            $deleted = $optionRepo->delete($option->id);
            
            if ($deleted) {
                wp_send_json_success(['message' => 'Option deleted successfully']);
            } else {
                throw new DatabaseException('Failed to delete option');
            }
            
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()], 500);
        }
    }
}