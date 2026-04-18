<?php
//src/Database/Repositories/QuestionRepository.php
declare(strict_types=1);

namespace SurveySphere\Database\Repositories;

use SurveySphere\Database\Models\Question;
use SurveySphere\Exceptions\DatabaseException;

final class QuestionRepository
{
    private string $table;
    
    public function __construct()
    {
        global $wpdb;
        $this->table = $wpdb->prefix . 'survey_sphere_questions';
    }
    
    public function findBySurveyId(int $surveyId): array
    {
        global $wpdb;
        
        $surveyQuestionTable = $wpdb->prefix . 'survey_sphere_survey_questions';
        
        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT q.* FROM {$this->table} q
                 INNER JOIN {$surveyQuestionTable} sq ON q.id = sq.question_id
                 WHERE sq.survey_id = %d
                 ORDER BY sq.order_index ASC",
                $surveyId
            ),
            ARRAY_A
        );
        
        return array_map([Question::class, 'fromArray'], $rows ?: []);
    }

    public function findByPublicId(string $publicId): ?Question
    {
        global $wpdb;
        
        $row = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$this->table} WHERE public_id = %s", $publicId),
            ARRAY_A
        );
        
        return $row ? Question::fromArray($row) : null;
    }
    
    public function create(array $data): ?Question
    {
        global $wpdb;
        
        $data['public_id'] = $this->generateNanoid();
        
        $result = $wpdb->insert(
            $this->table,
            [
                'public_id' => $data['public_id'],
                'segment_id' => isset($data['segment_id']) ? (int) $data['segment_id'] : null,
                'text' => sanitize_textarea_field($data['text']),
                'order_index' => (int) ($data['order_index'] ?? 0),
                'is_required' => (int) ($data['is_required'] ?? 1),
                'is_active' => (int) ($data['is_active'] ?? 1),
            ],
            ['%s', '%d', '%s', '%d', '%d', '%d']
        );
        
        if ($result === false) {
            throw new DatabaseException('Failed to create question: ' . $wpdb->last_error);
        }
        
        return $this->findById((int) $wpdb->insert_id);
    }
    
    private function generateNanoid(int $size = 21): string
    {
        $alphabet = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $id = '';
        
        for ($i = 0; $i < $size; $i++) {
            $id .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }
        
        return $id;
    }
    
    public function findById(int $id): ?Question
    {
        global $wpdb;
        
        $row = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$this->table} WHERE id = %d", $id),
            ARRAY_A
        );
        
        return $row ? Question::fromArray($row) : null;
    }
    
    public function findAll(?int $excludeSurveyId = null): array
    {
        global $wpdb;
        
        if ($excludeSurveyId) {
            $surveyQuestionTable = $wpdb->prefix . 'survey_sphere_survey_questions';
            $rows = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT q.* FROM {$this->table} q
                     WHERE q.id NOT IN (
                         SELECT question_id FROM {$surveyQuestionTable} WHERE survey_id = %d
                     )
                     ORDER BY q.created_at DESC",
                    $excludeSurveyId
                ),
                ARRAY_A
            );
        } else {
            $rows = $wpdb->get_results(
                "SELECT * FROM {$this->table} ORDER BY created_at DESC",
                ARRAY_A
            );
        }
        
        return array_map([Question::class, 'fromArray'], $rows ?: []);
    }
    
    public function delete(int $id): bool
    {
        global $wpdb;
        
        $result = $wpdb->delete(
            $this->table,
            ['id' => $id],
            ['%d']
        );
        
        if ($result === false) {
            throw new DatabaseException('Failed to delete question: ' . $wpdb->last_error);
        }
        
        return $result > 0;
    }
}