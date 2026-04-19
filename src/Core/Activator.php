<?php
//src/Core/Activator.php
declare(strict_types=1);

namespace SurveySphere\Core;

use SurveySphere\Database\Schema\Manager;
use SurveySphere\Security\CapabilityManager;

final class Activator
{
    public static function activate(): void
    {
        // Check PHP version
        if (version_compare(PHP_VERSION, '8.0', '<')) {
            deactivate_plugins(SURVEY_SPHERE_BASENAME);
            wp_die('Survey Sphere requires PHP 8.0 or higher.');
        }
        
        // Create database tables
        Manager::createAllTables();
        
        // Add capabilities
        CapabilityManager::addCapabilities();
        
        // Set version
        update_option('survey_sphere_version', SURVEY_SPHERE_VERSION);
        
        // Set default settings
        if (!get_option('survey_sphere_settings')) {
            update_option('survey_sphere_settings', [
                'email_from_name' => get_bloginfo('name'),
                'email_from_address' => get_option('admin_email'),
                'results_per_page' => 10,
                'enable_recaptcha' => false,
            ]);
        }
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
}