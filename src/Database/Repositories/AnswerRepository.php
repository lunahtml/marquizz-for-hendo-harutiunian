<?php
//src/Database/Repositories/AnswerRepository.php
declare(strict_types=1);

namespace SurveySphere\Database\Repositories;

use SurveySphere\Database\Models\Answer;
use SurveySphere\Exceptions\DatabaseException;

final class AnswerRepository
{
    private string $table;
    
    public function __construct()
    {
        global $wpdb;
        $this->table = $wpdb->prefix . 'survey_sphere_answers';
    }
    
    public function create(array $data): ?Answer
    {
        global $wpdb;
        
        $result = $wpdb->insert(
            $this->table,
            [
                'attempt_id' => (int)$data['attempt_id'],
                'question_id' => (int)$data['question_id'],
                'option_id' => (int)$data['option_id'],
            ],
            ['%d', '%d', '%d']
        );
        
        if ($result === false) {
            throw new DatabaseException('Failed to create answer: ' . $wpdb->last_error);
        }
        
        return $this->findById((int)$wpdb->insert_id);
    }
    
    public function findById(int $id): ?Answer
    {
        global $wpdb;
        
        $row = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$this->table} WHERE id = %d", $id),
            ARRAY_A
        );
        
        return $row ? Answer::fromArray($row) : null;
    }
}