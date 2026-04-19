<?php
//src/Admin/Assets/AdminAssets.php
declare(strict_types=1);

namespace SurveySphere\Admin\Assets;

class AdminAssets
{
    public static function enqueue(string $hook): void
    {
        $asset_file = include(SURVEY_SPHERE_PATH . 'react-src/build/index.asset.php');
        
        // Все страницы плагина (главная, редактирование, вопросы)
        if (strpos($hook, 'survey-sphere') !== false) {
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
    }
}