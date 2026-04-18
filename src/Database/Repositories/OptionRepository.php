<?php
//src/Database/Repositories/OptionRepository.php
declare(strict_types=1);

namespace SurveySphere\Database\Repositories;

use SurveySphere\Database\Models\Option;
use SurveySphere\Exceptions\DatabaseException;

final class OptionRepository
{
    private string $table;
    
    public function __construct()
    {
        global $wpdb;
        $this->table = $wpdb->prefix . 'survey_sphere_options';
    }
    
    public function findByQuestionId(int $questionId): array
    {
        global $wpdb;
        
        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$this->table} WHERE question_id = %d ORDER BY order_index ASC",
                $questionId
            ),
            ARRAY_A
        );
        
        return array_map([Option::class, 'fromArray'], $rows ?: []);
    }
    
    public function create(array $data): ?Option
    {
        global $wpdb;
        
        $data['public_id'] = $this->generateNanoid();
        
        $result = $wpdb->insert(
            $this->table,
            [
                'public_id' => $data['public_id'],
                'question_id' => (int) $data['question_id'],
                'text' => sanitize_text_field($data['text']),
                'score' => (float) ($data['score'] ?? 0),
                'order_index' => (int) ($data['order_index'] ?? 0),
                'recommendation_text' => sanitize_textarea_field($data['recommendation_text'] ?? ''),
                'recommendation_level' => sanitize_text_field($data['recommendation_level'] ?? null),
                'is_active' => (int) ($data['is_active'] ?? 1),
            ],
            ['%s', '%d', '%s', '%f', '%d', '%s', '%s', '%d']
        );
        
        if ($result === false) {
            throw new DatabaseException('Failed to create option: ' . $wpdb->last_error);
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
    
    public function findById(int $id): ?Option
    {
        global $wpdb;
        
        $row = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$this->table} WHERE id = %d", $id),
            ARRAY_A
        );
        
        return $row ? Option::fromArray($row) : null;
    }

    public function findByPublicId(string $publicId): ?Option
{
    global $wpdb;
    
    $row = $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM {$this->table} WHERE public_id = %s", $publicId),
        ARRAY_A
    );
    
    return $row ? Option::fromArray($row) : null;
}
    
    public function delete(int $id): bool
    {
        global $wpdb;
        
        return $wpdb->delete($this->table, ['id' => $id], ['%d']) !== false;
    }
}