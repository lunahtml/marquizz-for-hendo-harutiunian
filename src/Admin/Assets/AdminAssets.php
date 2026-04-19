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
            $asset_file = include(SURVEY_SPHERE_PATH . 'react-src/build/index.asset.php');
            
            wp_enqueue_script(
                'survey-sphere-react',
                SURVEY_SPHERE_URL . 'react-src/build/index.js',
                $asset_file['dependencies'],
                $asset_file['version'],
                true
            );
            
            wp_enqueue_style(
                'survey-sphere-react',
                SURVEY_SPHERE_URL . 'react-src/build/index.css',
                [],
                $asset_file['version']
            );
            
            wp_localize_script('survey-sphere-react', 'surveySphereAdmin', [
                'nonce' => wp_create_nonce('wp_rest'),
                'apiUrl' => rest_url('survey-sphere/v1'),
            ]);
        }
        
        // Surveys list page
        if (strpos($hook, 'survey-sphere') !== false && strpos($hook, 'survey-sphere-edit') === false) {
            wp_enqueue_style('survey-sphere-admin', SURVEY_SPHERE_URL . 'assets/admin/css/admin.css', [], SURVEY_SPHERE_VERSION);
            wp_enqueue_script('survey-sphere-admin', SURVEY_SPHERE_URL . 'assets/admin/js/admin.js', ['jquery'], SURVEY_SPHERE_VERSION, true);
        }
    }
}