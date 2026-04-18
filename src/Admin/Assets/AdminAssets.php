<?php
//src/Admin/Assets/AdminAssets.php
declare(strict_types=1);

namespace SurveySphere\Admin\Assets;

class AdminAssets
{
    public static function enqueue(string $hook): void
    {
        // Survey edit page
        if (strpos($hook, 'survey-sphere-edit') !== false) {
            wp_enqueue_style('survey-sphere-admin', SURVEY_SPHERE_URL . 'assets/admin/css/admin.css', [], SURVEY_SPHERE_VERSION);
            wp_enqueue_script('survey-sphere-editor', SURVEY_SPHERE_URL . 'assets/admin/js/survey-editor.js', ['jquery'], SURVEY_SPHERE_VERSION, true);
            wp_localize_script('survey-sphere-editor', 'surveySphereAdmin', [
                'nonce' => wp_create_nonce('survey_sphere_admin'),
            ]);
        }
        
        // Surveys list page
        if (strpos($hook, 'survey-sphere') !== false && strpos($hook, 'survey-sphere-edit') === false) {
            wp_enqueue_style('survey-sphere-admin', SURVEY_SPHERE_URL . 'assets/admin/css/admin.css', [], SURVEY_SPHERE_VERSION);
            wp_enqueue_script('survey-sphere-admin', SURVEY_SPHERE_URL . 'assets/admin/js/admin.js', ['jquery'], SURVEY_SPHERE_VERSION, true);
        }
    }
}