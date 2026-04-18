<?php
//src/Frontend/Shortcodes/SurveyShortcode.php

declare(strict_types=1);

namespace SurveySphere\Frontend\Shortcodes;

use SurveySphere\Core\Container;
use SurveySphere\Services\SurveyService;
use SurveySphere\Security\InputSanitizer;

final class SurveyShortcode
{
    private SurveyService $surveyService;
    
    public function __construct(Container $container)
    {
        $this->surveyService = $container->get(SurveyService::class);
    }
    
    public function render(array $atts): string
    {
        $atts = shortcode_atts([
            'id' => '',
            'chart' => 'polarArea',
        ], $atts);
        
        $publicId = InputSanitizer::text($atts['id']);
        
        if (empty($publicId)) {
            return '<p>' . esc_html__('Survey ID is required', 'survey-sphere') . '</p>';
        }
        
        try {
            $survey = $this->surveyService->getSurveyByPublicId($publicId);
            
            if (!$survey || !$survey->isActive) {
                return '<p>' . esc_html__('Survey not found or inactive', 'survey-sphere') . '</p>';
            }
            
            // Load questions and options
            $questionRepo = new \SurveySphere\Database\Repositories\QuestionRepository();
            $optionRepo = new \SurveySphere\Database\Repositories\OptionRepository();
            
            $questions = $questionRepo->findBySurveyId($survey->id);
            $optionsByQuestion = [];
            
            foreach ($questions as $question) {
                $optionsByQuestion[$question->id] = $optionRepo->findByQuestionId($question->id);
            }
            
            // Enqueue Chart.js
            wp_enqueue_script(
                'chart-js',
                'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js',
                [],
                '4.4.0',
                true
            );
            
            ob_start();
            include SURVEY_SPHERE_PATH . 'src/Frontend/Views/survey/wrapper.php';
            return ob_get_clean();
            
        } catch (\Exception $e) {
            if (WP_DEBUG) {
                return '<p>Error: ' . esc_html($e->getMessage()) . '</p>';
            }
            return '<p>' . esc_html__('Unable to load survey', 'survey-sphere') . '</p>';
        }
    }
}