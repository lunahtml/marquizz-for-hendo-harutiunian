<?php
//src/Admin/Controllers/SurveyController.php
declare(strict_types=1);

namespace SurveySphere\Admin\Controllers;

use SurveySphere\Services\SurveyService;
use SurveySphere\Database\Repositories\SurveyRepository;
use SurveySphere\Security\NonceManager;
use SurveySphere\Exceptions\ValidationException;

final class SurveyController
{
    private SurveyService $service;
    
    public function __construct()
    {
        $repository = new SurveyRepository();
        $this->service = new SurveyService($repository);
    }
    
    public function index(): void
    {
        $surveys = $this->service->getAllSurveys();
        include SURVEY_SPHERE_PATH . 'src/Admin/Views/surveys/list.php';
    }
    
    public function create(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleCreate();
        } else {
            include SURVEY_SPHERE_PATH . 'src/Admin/Views/surveys/create.php';
        }
    }

    public function edit(): void
{
    $publicId = $_GET['id'] ?? '';
    
    if (empty($publicId)) {
        wp_die('Survey ID is required');
    }
    
    $survey = $this->service->getSurveyByPublicId($publicId);
    
    if (!$survey) {
        wp_die('Survey not found');
    }
    
    include SURVEY_SPHERE_PATH . 'src/Admin/Views/surveys/edit.php';
}
    
    private function handleCreate(): void
    {
        try {
            // Verify nonce
            if (!NonceManager::verify('survey_sphere_create_survey', $_POST['_wpnonce'] ?? '')) {
                throw new ValidationException('Security check failed');
            }
            
            // Check permissions
            if (!current_user_can('create_surveys')) {
                wp_die('You do not have permission to create surveys');
            }
            
            // Validate input
            $name = sanitize_text_field($_POST['name'] ?? '');
            if (empty($name)) {
                throw new ValidationException('Survey name is required');
            }
            
            // Create survey
            $survey = $this->service->createSurvey([
                'name' => $name,
                'description' => sanitize_textarea_field($_POST['description'] ?? ''),
                'chart_type' => sanitize_text_field($_POST['chart_type'] ?? 'polar'),
            ]);
            
            // Redirect to edit page
            wp_redirect(add_query_arg([
                'page' => 'survey-sphere-edit',
                'id' => $survey->publicId,
                'message' => 'created'
            ], admin_url('admin.php')));
            exit;
            
        } catch (ValidationException $e) {
            $error = $e->getMessage();
            include SURVEY_SPHERE_PATH . 'src/Admin/Views/surveys/create.php';
        } catch (\Exception $e) {
            wp_die('Error creating survey: ' . esc_html($e->getMessage()));
        }
    }
}