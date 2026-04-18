<?php
//src\Frontend\AJAX\SubmitAttemptHandler.php
declare(strict_types=1);

namespace SurveySphere\Frontend\AJAX;

use SurveySphere\Database\Repositories\AttemptRepository;
use SurveySphere\Database\Repositories\RespondentRepository;
use SurveySphere\Database\Repositories\SurveyRepository;
use SurveySphere\Database\Repositories\QuestionRepository;
use SurveySphere\Database\Repositories\OptionRepository;
use SurveySphere\Database\Repositories\AnswerRepository;
use SurveySphere\Security\NonceManager;

final class SubmitAttemptHandler
{
    public static function handle(): void
    {
        if (!NonceManager::verify('survey_sphere_frontend', $_POST['_wpnonce'] ?? '')) {
            wp_send_json_error(['message' => 'Security check failed'], 403);
        }
        
        $email = sanitize_email($_POST['email'] ?? '');
        $surveyPublicId = sanitize_text_field($_POST['survey_id'] ?? '');
        $answers = $_POST['answers'] ?? [];
        
        if (empty($email) || !is_email($email)) {
            wp_send_json_error(['message' => 'Valid email is required'], 400);
        }
        
        if (empty($surveyPublicId)) {
            wp_send_json_error(['message' => 'Survey ID is required'], 400);
        }
        
        if (empty($answers) || !is_array($answers)) {
            wp_send_json_error(['message' => 'No answers provided'], 400);
        }
        
        try {
            // Найти или создать респондента
            $respondentRepo = new RespondentRepository();
            $respondent = $respondentRepo->findByEmail($email);
            
            if (!$respondent) {
                $respondent = $respondentRepo->create([
                    'name' => explode('@', $email)[0],
                    'email' => $email,
                ]);
            }
            
            // Найти опрос
            $surveyRepo = new SurveyRepository();
            $survey = $surveyRepo->findByPublicId($surveyPublicId);
            
            if (!$survey) {
                wp_send_json_error(['message' => 'Survey not found'], 404);
            }
            
            // Создать попытку
            $attemptRepo = new AttemptRepository();
            $attempt = $attemptRepo->create([
                'survey_id' => $survey->id,
                'respondent_id' => $respondent->id,
            ]);
            
            // Сохранить ответы
            $questionRepo = new QuestionRepository();
            $optionRepo = new OptionRepository();
            $answerRepo = new AnswerRepository();
            
            foreach ($answers as $questionPublicId => $optionPublicId) {
                $question = $questionRepo->findByPublicId($questionPublicId);
                $option = $optionRepo->findByPublicId($optionPublicId);
                
                if ($question && $option) {
                    $answerRepo->create([
                        'attempt_id' => $attempt->id,
                        'question_id' => $question->id,
                        'option_id' => $option->id,
                    ]);
                }
            }
            
            wp_send_json_success([
                'message' => 'Results saved successfully',
                'attempt_id' => $attempt->publicId,
                'respondent_email' => $respondent->email,
            ]);
            
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()], 500);
        }
    }
}