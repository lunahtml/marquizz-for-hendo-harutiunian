<?php
/**
 * Plugin Name: Survey Sphere
 * Plugin URI: https://survey-sphere.com
 * Description: Advanced survey builder with customizable charts and recommendations
 * Version: 1.0.0
 * Author: Anahita for Henrik Arutiunian
 * License: GPL v2 or later
 * Text Domain: survey-sphere
 * Domain Path: /languages
 * Requires PHP: 8.3
 * Requires at least: 6.0
 */

declare(strict_types=1);

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Plugin constants
define('SURVEY_SPHERE_VERSION', '1.0.0');
define('SURVEY_SPHERE_FILE', __FILE__);
define('SURVEY_SPHERE_PATH', plugin_dir_path(__FILE__));
define('SURVEY_SPHERE_URL', plugin_dir_url(__FILE__));
define('SURVEY_SPHERE_BASENAME', plugin_basename(__FILE__));

// Load Composer autoloader
if (file_exists(SURVEY_SPHERE_PATH . 'vendor/autoload.php')) {
    require_once SURVEY_SPHERE_PATH . 'vendor/autoload.php';
}

// Initialize plugin
if (class_exists('SurveySphere\\Core\\Plugin')) {
    SurveySphere\Core\Plugin::instance();
}

// Activation/Deactivation hooks
register_activation_hook(__FILE__, ['SurveySphere\\Core\\Activator', 'activate']);
register_deactivation_hook(__FILE__, ['SurveySphere\\Core\\Deactivator', 'deactivate']);