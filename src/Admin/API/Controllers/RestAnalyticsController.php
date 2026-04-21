<?php
declare(strict_types=1);

namespace SurveySphere\Admin\API\Controllers;

use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use SurveySphere\Database\Repositories\AttemptRepository;
use SurveySphere\Database\Repositories\RespondentRepository;
use SurveySphere\Database\Repositories\SurveyRepository;

final class RestAnalyticsController extends WP_REST_Controller
{
    public function __construct()
    {
        $this->namespace = 'survey-sphere/v1';
        $this->rest_base = 'analytics';
    }
    
    public function register_routes(): void
    {
        // Статистика по опросу
        register_rest_route($this->namespace, '/' . $this->rest_base . '/survey/(?P<survey_id>[\w-]+)', [
            [
                'methods' => 'GET',
                'callback' => [$this, 'get_survey_stats'],
                'permission_callback' => [$this, 'permissions_check'],
            ],
        ]);
        
        // Все попытки с фильтрацией
        register_rest_route($this->namespace, '/' . $this->rest_base . '/attempts', [
            [
                'methods' => 'GET',
                'callback' => [$this, 'get_attempts'],
                'permission_callback' => [$this, 'permissions_check'],
                'args' => [
                    'survey_id' => ['type' => 'string'],
                    'respondent_id' => ['type' => 'string'],
                    'date_from' => ['type' => 'string'],
                    'date_to' => ['type' => 'string'],
                    'page' => ['type' => 'integer', 'default' => 1],
                    'per_page' => ['type' => 'integer', 'default' => 20],
                ],
            ],
        ]);
        
        // Детали попытки
        register_rest_route($this->namespace, '/' . $this->rest_base . '/attempt/(?P<attempt_id>[\w-]+)', [
            [
                'methods' => 'GET',
                'callback' => [$this, 'get_attempt_details'],
                'permission_callback' => [$this, 'permissions_check'],
            ],
        ]);
        
        // Сводная статистика
        register_rest_route($this->namespace, '/' . $this->rest_base . '/summary', [
            [
                'methods' => 'GET',
                'callback' => [$this, 'get_summary'],
                'permission_callback' => [$this, 'permissions_check'],
            ],
        ]);
    }
    
    public function permissions_check($request): bool
    {
        return current_user_can('manage_survey_sphere');
    }
    
    public function get_survey_stats($request): WP_REST_Response|WP_Error
    {
        $surveyPublicId = $request->get_param('survey_id');
        
        $surveyRepo = new SurveyRepository();
        $survey = $surveyRepo->findByPublicId($surveyPublicId);
        
        if (!$survey) {
            return new WP_Error('not_found', 'Survey not found', ['status' => 404]);
        }
        
        global $wpdb;
        
        // Общая статистика
        $attemptsTable = $wpdb->prefix . 'survey_sphere_attempts';
        $respondentsTable = $wpdb->prefix . 'survey_sphere_respondents';
        $answersTable = $wpdb->prefix . 'survey_sphere_answers';
        $questionsTable = $wpdb->prefix . 'survey_sphere_questions';
        $optionsTable = $wpdb->prefix . 'survey_sphere_options';
        $surveyQuestionsTable = $wpdb->prefix . 'survey_sphere_survey_questions';
        $segmentsTable = $wpdb->prefix . 'survey_sphere_segments';
        
        // Количество попыток
        $totalAttempts = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$attemptsTable} WHERE survey_id = %d",
            $survey->id
        ));
        
        // Уникальные респонденты
        $uniqueRespondents = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT respondent_id) FROM {$attemptsTable} WHERE survey_id = %d AND respondent_id IS NOT NULL",
            $survey->id
        ));
        
        // Средний балл
        $avgScore = $this->calculateAverageScore($survey->id);
        
        // Распределение по датам
        $attemptsByDate = $wpdb->get_results($wpdb->prepare(
            "SELECT DATE(created_at) as date, COUNT(*) as count 
             FROM {$attemptsTable} 
             WHERE survey_id = %d 
             GROUP BY DATE(created_at) 
             ORDER BY date DESC 
             LIMIT 30",
            $survey->id
        ), ARRAY_A);
        
        // Результаты по сегментам (средние баллы)
        $segmentScores = $this->getSegmentAverages($survey->id);
        
        // Последние попытки
        $recentAttempts = $wpdb->get_results($wpdb->prepare(
            "SELECT a.id, a.public_id, a.created_at, r.email, r.name
             FROM {$attemptsTable} a
             LEFT JOIN {$respondentsTable} r ON a.respondent_id = r.id
             WHERE a.survey_id = %d
             ORDER BY a.created_at DESC
             LIMIT 10",
            $survey->id
        ), ARRAY_A);
        
        return new WP_REST_Response([
            'total_attempts' => (int) $totalAttempts,
            'unique_respondents' => (int) $uniqueRespondents,
            'average_score' => round($avgScore, 1),
            'attempts_by_date' => $attemptsByDate,
            'segment_scores' => $segmentScores,
            'recent_attempts' => array_map(function($a) {
                return [
                    'id' => $a['public_id'],
                    'created_at' => $a['created_at'],
                    'respondent' => [
                        'email' => $a['email'],
                        'name' => $a['name'] ?: explode('@', $a['email'])[0],
                    ],
                ];
            }, $recentAttempts),
        ], 200);
    }
    
    private function calculateAverageScore(int $surveyId): float
    {
        global $wpdb;
        $attemptsTable = $wpdb->prefix . 'survey_sphere_attempts';
        $answersTable = $wpdb->prefix . 'survey_sphere_answers';
        $optionsTable = $wpdb->prefix . 'survey_sphere_options';
        
        $attempts = $wpdb->get_results($wpdb->prepare(
            "SELECT id FROM {$attemptsTable} WHERE survey_id = %d",
            $surveyId
        ));
        
        if (empty($attempts)) return 0;
        
        $totalScore = 0;
        $totalMax = 0;
        
        foreach ($attempts as $attempt) {
            $answers = $wpdb->get_results($wpdb->prepare(
                "SELECT opt.score, q.max_score 
                 FROM {$answersTable} a
                 JOIN {$optionsTable} opt ON a.option_id = opt.id
                 JOIN (
                     SELECT q.id, MAX(opt2.score) as max_score
                     FROM {$wpdb->prefix}survey_sphere_questions q
                     JOIN {$wpdb->prefix}survey_sphere_options opt2 ON q.id = opt2.question_id
                     GROUP BY q.id
                 ) q ON a.question_id = q.id
                 WHERE a.attempt_id = %d",
                $attempt->id
            ), ARRAY_A);
            
            $attemptScore = 0;
            $attemptMax = 0;
            foreach ($answers as $ans) {
                $attemptScore += (float) $ans['score'];
                $attemptMax += (float) $ans['max_score'];
            }
            
            if ($attemptMax > 0) {
                $totalScore += ($attemptScore / $attemptMax) * 100;
                $totalMax += 100;
            }
        }
        
        return $totalMax > 0 ? $totalScore / count($attempts) : 0;
    }
    
    private function getSegmentAverages(int $surveyId): array
    {
        global $wpdb;
        $segmentsTable = $wpdb->prefix . 'survey_sphere_segments';
        $surveyQuestionsTable = $wpdb->prefix . 'survey_sphere_survey_questions';
        
        $segments = $wpdb->get_results($wpdb->prepare(
            "SELECT id, public_id, name, color FROM {$segmentsTable} WHERE survey_id = %d",
            $surveyId
        ), ARRAY_A);
        
        // TODO: Реализовать расчёт средних баллов по сегментам
        
        return array_map(function($s) {
            return [
                'id' => $s['public_id'],
                'name' => $s['name'],
                'color' => $s['color'],
                'average_score' => rand(30, 90), // Временно случайные данные
            ];
        }, $segments);
    }
    
    public function get_attempts($request): WP_REST_Response|WP_Error
    {
        global $wpdb;
    
        $surveyId = $request->get_param('survey_id');
        $page = $request->get_param('page');
        $perPage = $request->get_param('per_page');
        $offset = ($page - 1) * $perPage;
        
        $attemptsTable = $wpdb->prefix . 'survey_sphere_attempts';
        $respondentsTable = $wpdb->prefix . 'survey_sphere_respondents';
        $surveysTable = $wpdb->prefix . 'survey_sphere_surveys';
        
        $where = ['1=1'];
        $params = [];
        
        if ($surveyId) {
            $surveyRepo = new SurveyRepository();
            $survey = $surveyRepo->findByPublicId($surveyId);
            if ($survey) {
                $where[] = "a.survey_id = %d";
                $params[] = $survey->id;
            }
        }
        
        $whereSql = implode(' AND ', $where);
        
        // Сначала получаем total
        $total = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$attemptsTable} a WHERE {$whereSql}",
            $params
        ));
        
        // Затем получаем данные с LIMIT/OFFSET
        $sql = "SELECT a.public_id, a.created_at, 
                       r.email, r.name,
                       s.name as survey_name, s.public_id as survey_public_id
                FROM {$attemptsTable} a
                LEFT JOIN {$respondentsTable} r ON a.respondent_id = r.id
                LEFT JOIN {$surveysTable} s ON a.survey_id = s.id
                WHERE {$whereSql}
                ORDER BY a.created_at DESC
                LIMIT %d OFFSET %d";
        
        $queryParams = array_merge($params, [$perPage, $offset]);
        $attempts = $wpdb->get_results($wpdb->prepare($sql, $queryParams), ARRAY_A);
        
        error_log("SQL: " . $wpdb->prepare($sql, $queryParams));
        error_log("Attempts found: " . count($attempts));
        
        $data = array_map(function($a) {
            return [
                'id' => $a['public_id'],
                'created_at' => $a['created_at'],
                'survey' => [
                    'id' => $a['survey_public_id'],
                    'name' => $a['survey_name'],
                ],
                'respondent' => [
                    'email' => $a['email'],
                    'name' => $a['name'] ?: ($a['email'] ? explode('@', $a['email'])[0] : 'Anonymous'),
                ],
            ];
        }, $attempts);
        
        return new WP_REST_Response([
            'attempts' => $data,
            'total' => (int) $total,
            'page' => $page,
            'per_page' => $perPage,
        ], 200);
    }
    
    public function get_attempt_details($request): WP_REST_Response|WP_Error
    {
        $attemptPublicId = $request->get_param('attempt_id');
        
        $attemptRepo = new AttemptRepository();
        $attempt = $attemptRepo->findByPublicId($attemptPublicId);
        
        if (!$attempt) {
            return new WP_Error('not_found', 'Attempt not found', ['status' => 404]);
        }
        
        global $wpdb;
        
        // Данные попытки
        $attemptData = $wpdb->get_row($wpdb->prepare(
            "SELECT a.*, r.email, r.name, s.name as survey_name
             FROM {$wpdb->prefix}survey_sphere_attempts a
             LEFT JOIN {$wpdb->prefix}survey_sphere_respondents r ON a.respondent_id = r.id
             LEFT JOIN {$wpdb->prefix}survey_sphere_surveys s ON a.survey_id = s.id
             WHERE a.id = %d",
            $attempt->id
        ), ARRAY_A);
        
        // Ответы
        $answers = $wpdb->get_results($wpdb->prepare(
            "SELECT q.text as question_text, opt.text as option_text, opt.score,
                    seg.name as segment_name, seg.color as segment_color
             FROM {$wpdb->prefix}survey_sphere_answers ans
             JOIN {$wpdb->prefix}survey_sphere_questions q ON ans.question_id = q.id
             JOIN {$wpdb->prefix}survey_sphere_options opt ON ans.option_id = opt.id
             LEFT JOIN {$wpdb->prefix}survey_sphere_survey_questions sq ON q.id = sq.question_id AND sq.survey_id = %d
             LEFT JOIN {$wpdb->prefix}survey_sphere_segments seg ON sq.segment_id = seg.id
             WHERE ans.attempt_id = %d",
            $attempt->surveyId, $attempt->id
        ), ARRAY_A);
        
        // Расчёт баллов
        $totalScore = 0;
        $totalMax = 0;
        $segmentScores = [];
        
        foreach ($answers as $ans) {
            $totalScore += (float) $ans['score'];
            $segName = $ans['segment_name'] ?: 'Uncategorized';
            if (!isset($segmentScores[$segName])) {
                $segmentScores[$segName] = ['score' => 0, 'max' => 0, 'color' => $ans['segment_color']];
            }
            $segmentScores[$segName]['score'] += (float) $ans['score'];
        }
        
        // Получаем максимальные баллы
        $maxScores = $wpdb->get_results($wpdb->prepare(
            "SELECT q.id, MAX(opt.score) as max_score
             FROM {$wpdb->prefix}survey_sphere_survey_questions sq
             JOIN {$wpdb->prefix}survey_sphere_questions q ON sq.question_id = q.id
             JOIN {$wpdb->prefix}survey_sphere_options opt ON q.id = opt.question_id
             WHERE sq.survey_id = %d
             GROUP BY q.id",
            $attempt->surveyId
        ), ARRAY_A);
        
        foreach ($maxScores as $ms) {
            $totalMax += (float) $ms['max_score'];
        }
        
        $overallScore = $totalMax > 0 ? round(($totalScore / $totalMax) * 100) : 0;
        
        return new WP_REST_Response([
            'id' => $attemptData['public_id'],
            'created_at' => $attemptData['created_at'],
            'survey' => ['name' => $attemptData['survey_name']],
            'respondent' => [
                'email' => $attemptData['email'],
                'name' => $attemptData['name'] ?: explode('@', $attemptData['email'])[0],
            ],
            'overall_score' => $overallScore,
            'segment_scores' => $segmentScores,
            'answers' => array_map(function($a) {
                return [
                    'question' => $a['question_text'],
                    'answer' => $a['option_text'],
                    'score' => (float) $a['score'],
                    'segment' => $a['segment_name'],
                ];
            }, $answers),
        ], 200);
    }
    
    public function get_summary($request): WP_REST_Response|WP_Error
    {
        global $wpdb;
        
        $attemptsTable = $wpdb->prefix . 'survey_sphere_attempts';
        $respondentsTable = $wpdb->prefix . 'survey_sphere_respondents';
        $surveysTable = $wpdb->prefix . 'survey_sphere_surveys';
        
        $totalAttempts = $wpdb->get_var("SELECT COUNT(*) FROM {$attemptsTable}");
        $totalRespondents = $wpdb->get_var("SELECT COUNT(*) FROM {$respondentsTable}");
        $totalSurveys = $wpdb->get_var("SELECT COUNT(*) FROM {$surveysTable}");
        
        // Попытки по дням (последние 30 дней)
        $attemptsByDay = $wpdb->get_results(
            "SELECT DATE(created_at) as date, COUNT(*) as count 
             FROM {$attemptsTable} 
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
             GROUP BY DATE(created_at) 
             ORDER BY date ASC",
            ARRAY_A
        );
        
        // Топ опросов
        $topSurveys = $wpdb->get_results(
            "SELECT s.name, COUNT(a.id) as attempts 
             FROM {$surveysTable} s 
             LEFT JOIN {$attemptsTable} a ON s.id = a.survey_id 
             GROUP BY s.id 
             ORDER BY attempts DESC 
             LIMIT 5",
            ARRAY_A
        );
        
        return new WP_REST_Response([
            'total_attempts' => (int) $totalAttempts,
            'total_respondents' => (int) $totalRespondents,
            'total_surveys' => (int) $totalSurveys,
            'attempts_by_day' => $attemptsByDay,
            'top_surveys' => $topSurveys,
        ], 200);
    }
}