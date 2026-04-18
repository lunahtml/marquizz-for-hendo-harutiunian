<?php
//src/Admin/AJAX/UpdateChartTypeHandler.php

declare(strict_types=1);

namespace SurveySphere\Admin\AJAX;

use SurveySphere\Database\Repositories\SurveyRepository;
use SurveySphere\Security\NonceManager;

final class UpdateChartTypeHandler
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
        $chartType = sanitize_text_field($_POST['chart_type'] ?? 'polarArea');
        
        // Обновлённый список допустимых типов
        $allowedTypes = ['polarArea', 'radar', 'doughnut', 'bar'];
        if (!in_array($chartType, $allowedTypes)) {
            wp_send_json_error(['message' => 'Invalid chart type'], 400);
        }
        
        try {
            $surveyRepo = new SurveyRepository();
            $survey = $surveyRepo->findByPublicId($surveyPublicId);
            
            if (!$survey) {
                wp_send_json_error(['message' => 'Survey not found'], 404);
            }
            
            $updated = $surveyRepo->update($survey->id, ['chart_type' => $chartType]);
            
            if ($updated) {
                wp_send_json_success(['message' => 'Chart type updated']);
            } else {
                wp_send_json_error(['message' => 'Failed to update chart type'], 500);
            }
            
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()], 500);
        }
    }
}