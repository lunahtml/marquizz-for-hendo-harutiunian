<?php
//src/Core/Plugin.php
declare(strict_types=1);

namespace SurveySphere\Core;

use SurveySphere\Admin\Menu\AdminMenu;
use SurveySphere\Admin\Assets\AdminAssets;
use SurveySphere\Frontend\Shortcodes\SurveyShortcode;
use SurveySphere\Database\Repositories\SurveyRepository;
use SurveySphere\Database\Repositories\QuestionRepository;
use SurveySphere\Services\SurveyService;
use SurveySphere\Admin\API\Controllers\RestSegmentController;

final class Plugin
{
    private static ?self $instance = null;
    private Container $container;

    private function __construct()
    {
        $this->container = new Container();
        $this->registerServices();
        $this->initHooks();
    }

    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function registerServices(): void
    {
        $this->container->set(SurveyRepository::class, fn() => new SurveyRepository());
        $this->container->set(QuestionRepository::class, fn() => new QuestionRepository());
        $this->container->set(SurveyService::class, fn($c) => new SurveyService(
            $c->get(SurveyRepository::class)
        ));
    }

    private function initHooks(): void
    {
        add_action('init', [$this, 'init']);
        add_action('rest_api_init', [$this, 'registerRestRoutes']);
        add_action('admin_menu', [$this, 'adminMenu']);
        add_action('admin_enqueue_scripts', [$this, 'adminAssets']);
        add_action('wp_enqueue_scripts', [$this, 'frontendAssets']);
        
        // Admin AJAX handlers
        add_action('wp_ajax_survey_sphere_save_question', ['SurveySphere\Admin\AJAX\SaveQuestionHandler', 'handle']);
        add_action('wp_ajax_survey_sphere_save_option', ['SurveySphere\Admin\AJAX\SaveOptionHandler', 'handle']);
        add_action('wp_ajax_survey_sphere_delete_question', ['SurveySphere\Admin\AJAX\DeleteQuestionHandler', 'handle']);
        add_action('wp_ajax_survey_sphere_delete_option', ['SurveySphere\Admin\AJAX\DeleteOptionHandler', 'handle']);
        add_action('wp_ajax_survey_sphere_update_chart_type', ['SurveySphere\Admin\AJAX\UpdateChartTypeHandler', 'handle']);
        add_action('wp_ajax_survey_sphere_get_questions', ['SurveySphere\Admin\AJAX\GetQuestionsHandler', 'handle']);
        add_action('wp_ajax_survey_sphere_attach_question', ['SurveySphere\Admin\AJAX\AttachQuestionHandler', 'handle']);
        
        // Frontend AJAX handlers
        add_action('wp_ajax_survey_sphere_submit_attempt', ['SurveySphere\Frontend\AJAX\SubmitAttemptHandler', 'handle']);
        add_action('wp_ajax_nopriv_survey_sphere_submit_attempt', ['SurveySphere\Frontend\AJAX\SubmitAttemptHandler', 'handle']);
        
        // Register shortcodes
        add_shortcode('survey_sphere', [new SurveyShortcode($this->container), 'render']);
    }

    // public function registerRestRoutes(): void
    // {
    //     $segmentController = new RestSegmentController();
    //     $segmentController->register_routes();
    // }
    public function registerRestRoutes(): void
    {
        $segmentController = new \SurveySphere\Admin\API\Controllers\RestSegmentController();
        $segmentController->register_routes();
        
        $questionController = new \SurveySphere\Admin\API\Controllers\RestQuestionController();
        $questionController->register_routes();
        
        $surveyQuestionsController = new \SurveySphere\Admin\API\Controllers\RestSurveyQuestionsController();
        $surveyQuestionsController->register_routes();

        $surveyController = new \SurveySphere\Admin\API\Controllers\RestSurveyController();
        $surveyController->register_routes();
    }
    public function init(): void
    {
        load_plugin_textdomain('survey-sphere', false, dirname(SURVEY_SPHERE_BASENAME) . '/languages');
        
        // Включаем cookie-аутентификацию для REST API
        add_filter('rest_authentication_errors', function($result) {
            if (!empty($result)) {
                return $result;
            }
            
            if (is_user_logged_in()) {
                return true;
            }
            
            return $result;
        });
    }

    public function adminMenu(): void
    {
        new AdminMenu();
    }

    public function adminAssets(string $hook): void
    {
        wp_localize_script('wp-api', 'wpApiSettings', [
            'root' => esc_url_raw(rest_url()),
            'nonce' => wp_create_nonce('wp_rest'),
        ]);
        
        AdminAssets::enqueue($hook);
    }

    public function frontendAssets(): void
    {
        if (has_shortcode(get_post()->post_content ?? '', 'survey_sphere')) {
            wp_enqueue_style(
                'survey-sphere-frontend',
                SURVEY_SPHERE_URL . 'assets/frontend/css/survey.css',
                [],
                SURVEY_SPHERE_VERSION
            );
            
            wp_enqueue_script(
                'survey-sphere-frontend',
                SURVEY_SPHERE_URL . 'assets/frontend/js/survey.js',
                [],
                SURVEY_SPHERE_VERSION,
                true
            );
            
            wp_localize_script('survey-sphere-frontend', 'surveySphereData', [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('survey_sphere_frontend'),
                'pleaseSelect' => __('Please select an answer', 'survey-sphere'),
                'answerAll' => __('Please answer all questions', 'survey-sphere'),
            ]);
        }
    }

    public function getContainer(): Container
    {
        return $this->container;
    }
}