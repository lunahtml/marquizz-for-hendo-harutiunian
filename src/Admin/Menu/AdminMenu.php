<?php
//src/Admin/Menu/AdminMenu.php

declare(strict_types=1);

namespace SurveySphere\Admin\Menu;

use SurveySphere\Admin\Controllers\SurveyController;

final class AdminMenu
{
    public function __construct()
    {
  
        $this->register();
   
    }
    
    public function register(): void
    {

        
        // Main menu
        add_menu_page(
            __('Survey Sphere', 'survey-sphere'),
            __('Surveys', 'survey-sphere'),
            'manage_survey_sphere',
            'survey-sphere',
            [$this, 'renderMainPage'],
            'dashicons-chart-pie',
            30
        );
        
        // Surveys list
        add_submenu_page(
            'survey-sphere',
            __('All Surveys', 'survey-sphere'),
            __('All Surveys', 'survey-sphere'),
            'manage_survey_sphere',
            'survey-sphere',
            [$this, 'renderMainPage']
        );
        
        // Add new survey
        add_submenu_page(
            'survey-sphere',
            __('Add New Survey', 'survey-sphere'),
            __('Add New', 'survey-sphere'),
            'create_surveys',
            'survey-sphere-add',
            [new SurveyController(), 'create']
        );
        
// Analytics
add_submenu_page(
    'survey-sphere',
    __('Analytics', 'survey-sphere'),
    __('Analytics', 'survey-sphere'),
    'manage_survey_sphere',
    'survey-sphere-analytics',
    [$this, 'renderAnalytics']
);

        // Settings
// Questions Library (показывается в меню)
add_submenu_page(
    'survey-sphere',
    __('Questions Library', 'survey-sphere'),
    __('Questions', 'survey-sphere'),
    'manage_survey_sphere',
    'survey-sphere-questions',
    [new SurveyController(), 'questions']
);

// Edit survey (скрытая)
add_submenu_page(
    null,
    __('Edit Survey', 'survey-sphere'),
    __('Edit Survey', 'survey-sphere'),
    'manage_survey_sphere',
    'survey-sphere-edit',
    [new SurveyController(), 'edit']
);
        

    }
    
    public function renderMainPage(): void
    {
        $controller = new SurveyController();
        $controller->index();
    }
    
    public function renderSettings(): void
    {
        echo '<div class="wrap"><h1>Settings</h1><p>Settings page coming soon.</p></div>';
    }
    public function renderAnalytics(): void
{
    include SURVEY_SPHERE_PATH . 'src/Admin/Views/analytics/dashboard.php';
}
}