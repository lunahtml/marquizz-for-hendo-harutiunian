<?php
//src/Database/Repositories/SurveyQuestionRepository.php
declare(strict_types=1);

namespace SurveySphere\Database\Repositories;

use SurveySphere\Exceptions\DatabaseException;

final class SurveyQuestionRepository
{
    private string $table;
    
    public function __construct()
    {
        global $wpdb;
        $this->table = $wpdb->prefix . 'survey_sphere_survey_questions';
    }
    
    public function attachQuestion(int $surveyId, int $questionId, int $orderIndex = 0): bool
    {
        global $wpdb;
        
        $result = $wpdb->insert(
            $this->table,
            [
                'survey_id' => $surveyId,
                'question_id' => $questionId,
                'order_index' => $orderIndex,
            ],
            ['%d', '%d', '%d']
        );
        
        if ($result === false) {
            throw new DatabaseException('Failed to attach question: ' . $wpdb->last_error);
        }
        
        return true;
    }
    
    public function detachQuestion(int $surveyId, int $questionId): bool
    {
        global $wpdb;
        
        $result = $wpdb->delete(
            $this->table,
            [
                'survey_id' => $surveyId,
                'question_id' => $questionId,
            ],
            ['%d', '%d']
        );
        
        return $result !== false;
    }
    
    public function getQuestionIdsForSurvey(int $surveyId): array
    {
        global $wpdb;
        
        return $wpdb->get_col(
            $wpdb->prepare(
                "SELECT question_id FROM {$this->table} WHERE survey_id = %d ORDER BY order_index ASC",
                $surveyId
            )
        );
    }
    
    public function getSurveyIdsForQuestion(int $questionId): array
    {
        global $wpdb;
        
        return $wpdb->get_col(
            $wpdb->prepare(
                "SELECT survey_id FROM {$this->table} WHERE question_id = %d",
                $questionId
            )
        );
    }
}