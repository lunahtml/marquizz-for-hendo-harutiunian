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
    
    public function attachQuestion(int $surveyId, int $questionId, int $orderIndex = 0, ?int $segmentId = null): bool
    {
        global $wpdb;
        
        $result = $wpdb->insert(
            $this->table,
            [
                'survey_id' => $surveyId,
                'question_id' => $questionId,
                'segment_id' => $segmentId,
                'order_index' => $orderIndex,
            ],
            ['%d', '%d', '%d', '%d']
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
    
    public function updateSegment(int $surveyId, int $questionId, ?int $segmentId): bool
    {
        global $wpdb;
        
        $result = $wpdb->update(
            $this->table,
            ['segment_id' => $segmentId],
            [
                'survey_id' => $surveyId,
                'question_id' => $questionId,
            ],
            ['%d'],
            ['%d', '%d']
        );
        
        if ($result === false) {
            throw new DatabaseException('Failed to update segment: ' . $wpdb->last_error);
        }
        
        return true;
    }
    
    public function updateOrder(int $surveyId, int $questionId, int $orderIndex): bool
    {
        global $wpdb;
        
        $result = $wpdb->update(
            $this->table,
            ['order_index' => $orderIndex],
            [
                'survey_id' => $surveyId,
                'question_id' => $questionId,
            ],
            ['%d'],
            ['%d', '%d']
        );
        
        if ($result === false) {
            throw new DatabaseException('Failed to update order: ' . $wpdb->last_error);
        }
        
        return true;
    }
    
    public function getQuestionIdsForSurvey(int $surveyId, ?int $segmentId = null): array
    {
        global $wpdb;
        
        $sql = "SELECT question_id FROM {$this->table} WHERE survey_id = %d";
        $params = [$surveyId];
        
        if ($segmentId !== null) {
            $sql .= " AND segment_id = %d";
            $params[] = $segmentId;
        }
        
        $sql .= " ORDER BY order_index ASC";
        
        return $wpdb->get_col($wpdb->prepare($sql, ...$params));
    }
    
    public function getQuestionsWithSegments(int $surveyId): array
    {
        global $wpdb;
        
        $questionsTable = $wpdb->prefix . 'survey_sphere_questions';
        
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT sq.*, q.text, q.public_id as question_public_id
                 FROM {$this->table} sq
                 INNER JOIN {$questionsTable} q ON sq.question_id = q.id
                 WHERE sq.survey_id = %d
                 ORDER BY sq.segment_id, sq.order_index ASC",
                $surveyId
            ),
            ARRAY_A
        ) ?: [];
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