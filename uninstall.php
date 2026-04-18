<?php
/**
 * Uninstall handler - removes all plugin data
 */

declare(strict_types=1);

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Load autoloader if exists
$autoloader = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloader)) {
    require_once $autoloader;
}

// Use Schema Manager to drop tables
if (class_exists('SurveySphere\\Database\\Schema\\Manager')) {
    SurveySphere\Database\Schema\Manager::dropAllTables();
}

// Clean options
delete_option('survey_sphere_version');
delete_option('survey_sphere_settings');
delete_option('survey_sphere_db_version');

// Clear any scheduled hooks
wp_clear_scheduled_hook('survey_sphere_daily_cleanup');