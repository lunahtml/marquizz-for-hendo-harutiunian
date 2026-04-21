<?php
//src/Database/Repositories/RecommendationRepository.php
declare(strict_types=1);

namespace SurveySphere\Database\Repositories;

use SurveySphere\Database\Models\Recommendation;
use SurveySphere\Exceptions\DatabaseException;

final class RecommendationRepository
{
    private string $table;
    
    public function __construct()
    {
        global $wpdb;
        $this->table = $wpdb->prefix . 'survey_sphere_recommendations';
    }
    
    public function findByPublicId(string $publicId): ?Recommendation
    {
        global $wpdb;
        
        $row = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$this->table} WHERE public_id = %s", $publicId),
            ARRAY_A
        );
        
        return $row ? Recommendation::fromArray($row) : null;
    }
    
    public function findBySurveyId(int $surveyId, ?int $segmentId = null): array
    {
        global $wpdb;
        
        $sql = "SELECT * FROM {$this->table} WHERE survey_id = %d AND is_active = 1";
        $params = [$surveyId];
        
        if ($segmentId !== null) {
            $sql .= " AND segment_id = %d";
            $params[] = $segmentId;
        } else {
            $sql .= " AND segment_id IS NULL";
        }
        
        $sql .= " ORDER BY order_index ASC, min_score ASC";
        
        $rows = $wpdb->get_results($wpdb->prepare($sql, ...$params), ARRAY_A);
        
        return array_map([Recommendation::class, 'fromArray'], $rows ?: []);
    }
    
    public function findForScore(int $surveyId, int $score, ?int $segmentId = null): ?Recommendation
    {
        global $wpdb;
        
        $sql = "SELECT * FROM {$this->table} 
                WHERE survey_id = %d 
                AND is_active = 1 
                AND min_score <= %d 
                AND max_score >= %d";
        $params = [$surveyId, $score, $score];
        
        if ($segmentId !== null) {
            $sql .= " AND segment_id = %d";
            $params[] = $segmentId;
        } else {
            $sql .= " AND segment_id IS NULL";
        }
        
        $sql .= " ORDER BY order_index ASC LIMIT 1";
        
        $row = $wpdb->get_row($wpdb->prepare($sql, ...$params), ARRAY_A);
        
        return $row ? Recommendation::fromArray($row) : null;
    }
    
    public function findById(int $id): ?Recommendation
    {
        global $wpdb;
        
        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->table} WHERE id = %d", $id), ARRAY_A);
        
        return $row ? Recommendation::fromArray($row) : null;
    }
    
    public function create(array $data): ?Recommendation
    {
        global $wpdb;
        
        $data['public_id'] = $this->generateNanoid();
        
        $result = $wpdb->insert(
            $this->table,
            [
                'public_id' => $data['public_id'],
                'survey_id' => (int)$data['survey_id'],
                'segment_id' => isset($data['segment_id']) ? (int)$data['segment_id'] : null,
                'min_score' => (int)($data['min_score'] ?? 0),
                'max_score' => (int)($data['max_score'] ?? 100),
                'title' => sanitize_text_field($data['title']),
                'description' => sanitize_textarea_field($data['description'] ?? ''),
                'action_text' => sanitize_text_field($data['action_text'] ?? null),
                'action_url' => sanitize_text_field($data['action_url'] ?? null),
                'order_index' => (int)($data['order_index'] ?? 0),
                'is_active' => (int)($data['is_active'] ?? 1),
            ],
            ['%s', '%d', '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%d', '%d']
        );
        
        if ($result === false) {
            throw new DatabaseException('Failed to create recommendation: ' . $wpdb->last_error);
        }
        
        return $this->findById((int)$wpdb->insert_id);
    }
    
    public function update(int $id, array $data): ?Recommendation
    {
        global $wpdb;
        
        $updateData = [];
        $formats = [];
        
        $fields = ['min_score', 'max_score', 'title', 'description', 'action_text', 'action_url', 'order_index', 'is_active', 'segment_id'];
        
        foreach ($fields as $field) {
            if (array_key_exists($field, $data)) {
                $value = $data[$field];
                
                // Для segment_id: null или число
                if ($field === 'segment_id') {
                    if ($value === null || $value === '') {
                        $updateData[$field] = null;
                        $formats[] = '%s'; // NULL передаётся как строка
                    } else {
                        $updateData[$field] = (int) $value;
                        $formats[] = '%d';
                    }
                }
                // Для текстовых полей
                elseif ($field === 'title' || $field === 'description' || $field === 'action_text' || $field === 'action_url') {
                    $updateData[$field] = sanitize_text_field((string) $value);
                    $formats[] = '%s';
                }
                // Для числовых полей
                else {
                    $updateData[$field] = (int) $value;
                    $formats[] = '%d';
                }
            }
        }
        
        if (empty($updateData)) {
            return $this->findById($id);
        }
        
        $result = $wpdb->update(
            $this->table,
            $updateData,
            ['id' => $id],
            $formats,
            ['%d']
        );
        
        if ($result === false) {
            throw new DatabaseException('Failed to update recommendation: ' . $wpdb->last_error);
        }
        
        return $this->findById($id);
    }
    
    public function delete(int $id): bool
    {
        global $wpdb;
        
        $result = $wpdb->delete($this->table, ['id' => $id], ['%d']);
        
        if ($result === false) {
            throw new DatabaseException('Failed to delete recommendation: ' . $wpdb->last_error);
        }
        
        return $result > 0;
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
}