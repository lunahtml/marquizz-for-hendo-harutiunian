<?php
//src/Database/Repositories/SegmentRepository.php
declare(strict_types=1);

namespace SurveySphere\Database\Repositories;

use SurveySphere\Database\Models\Segment;
use SurveySphere\Exceptions\DatabaseException;

final class SegmentRepository
{
    private string $table;
    
    public function __construct()
    {
        global $wpdb;
        $this->table = $wpdb->prefix . 'survey_sphere_segments';
    }
    
    public function findBySurveyId(int $surveyId): array
    {
        global $wpdb;
        
        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$this->table} WHERE survey_id = %d AND is_active = 1 ORDER BY order_index ASC",
                $surveyId
            ),
            ARRAY_A
        );
        
        return array_map([Segment::class, 'fromArray'], $rows ?: []);
    }
    
    public function findByPublicId(string $publicId): ?Segment
    {
        global $wpdb;
        
        $row = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$this->table} WHERE public_id = %s", $publicId),
            ARRAY_A
        );
        
        return $row ? Segment::fromArray($row) : null;
    }
    
    public function create(array $data): ?Segment
    {
        global $wpdb;
        
        $data['public_id'] = $this->generateNanoid();
        
        $result = $wpdb->insert(
            $this->table,
            [
                'public_id' => $data['public_id'],
                'survey_id' => (int)$data['survey_id'],
                'name' => sanitize_text_field($data['name']),
                'description' => sanitize_textarea_field($data['description'] ?? null),
                'icon' => sanitize_text_field($data['icon'] ?? null),
                'color' => sanitize_text_field($data['color'] ?? '#36A2EB'),
                'order_index' => (int)($data['order_index'] ?? 0),
                'is_active' => (int)($data['is_active'] ?? 1),
            ],
            ['%s', '%d', '%s', '%s', '%s', '%s', '%d', '%d']
        );
        
        if ($result === false) {
            throw new DatabaseException('Failed to create segment: ' . $wpdb->last_error);
        }
        
        return $this->findById((int)$wpdb->insert_id);
    }
    
    public function update(int $id, array $data): ?Segment
    {
        global $wpdb;
        
        $updateData = [];
        $formats = [];
        
        if (isset($data['name'])) {
            $updateData['name'] = sanitize_text_field($data['name']);
            $formats[] = '%s';
        }
        
        if (isset($data['description'])) {
            $updateData['description'] = sanitize_textarea_field($data['description']);
            $formats[] = '%s';
        }
        
        if (isset($data['icon'])) {
            $updateData['icon'] = sanitize_text_field($data['icon']);
            $formats[] = '%s';
        }
        
        if (isset($data['color'])) {
            $updateData['color'] = sanitize_text_field($data['color']);
            $formats[] = '%s';
        }
        
        if (isset($data['order_index'])) {
            $updateData['order_index'] = (int)$data['order_index'];
            $formats[] = '%d';
        }
        
        if (isset($data['is_active'])) {
            $updateData['is_active'] = (int)$data['is_active'];
            $formats[] = '%d';
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
            throw new DatabaseException('Failed to update segment: ' . $wpdb->last_error);
        }
        
        return $this->findById($id);
    }
    
    public function delete(int $id): bool
    {
        global $wpdb;
        
        $result = $wpdb->delete($this->table, ['id' => $id], ['%d']);
        
        if ($result === false) {
            throw new DatabaseException('Failed to delete segment: ' . $wpdb->last_error);
        }
        
        return $result > 0;
    }
    
    public function findById(int $id): ?Segment
    {
        global $wpdb;
        
        $row = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$this->table} WHERE id = %d", $id),
            ARRAY_A
        );
        
        return $row ? Segment::fromArray($row) : null;
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