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
            
            $questionRepo = new \SurveySphere\Database\Repositories\QuestionRepository();
            $optionRepo = new \SurveySphere\Database\Repositories\OptionRepository();
            $segmentRepo = new \SurveySphere\Database\Repositories\SegmentRepository();
            
            $questions = $questionRepo->findBySurveyId($survey->id);
            $segments = $segmentRepo->findBySurveyId($survey->id);
            $optionsByQuestion = [];
            
            foreach ($questions as $question) {
                $optionsByQuestion[$question->id] = $optionRepo->findByQuestionId($question->id);
            }
            
            wp_enqueue_style(
                'survey-sphere-frontend',
                SURVEY_SPHERE_URL . 'react-src/build/frontend.css',
                [],
                SURVEY_SPHERE_VERSION
            );
            
            wp_enqueue_script(
                'survey-sphere-frontend',
                SURVEY_SPHERE_URL . 'react-src/build/frontend.js',
                ['wp-element'],
                SURVEY_SPHERE_VERSION,
                true
            );
            
            $surveyData = [
                'survey' => [
                    'id' => $survey->publicId,
                    'name' => $survey->name,
                    'description' => $survey->description,
                    'chartType' => $survey->chartType,
                ],
                'questions' => array_map(function($q) use ($optionRepo) {
                    $segmentId = $q->segmentId ?? null;
                    return [
                        'id' => $q->publicId,
                        'text' => $q->text,
                        'segmentId' => $segmentId,
                        'options' => array_map(function($opt) {
                            return [
                                'id' => $opt->publicId,
                                'text' => $opt->text,
                                'score' => $opt->score,
                            ];
                        }, $optionRepo->findByQuestionId($q->id)),
                    ];
                }, $questions),
                'segments' => array_map(function($seg) {
                    return [
                        'id' => $seg->publicId,
                        'name' => $seg->name,
                        'color' => $seg->color,
                    ];
                }, $segments),
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('survey_sphere_frontend'),
            ];
            
 
            
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