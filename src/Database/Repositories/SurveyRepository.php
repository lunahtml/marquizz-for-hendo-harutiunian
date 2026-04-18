<?php
//src/Database/Repositories/SurveyRepository.php
declare(strict_types=1);

namespace SurveySphere\Database\Repositories;

use SurveySphere\Database\Models\Survey;
use SurveySphere\Exceptions\DatabaseException;

final class SurveyRepository
{
    private string $table;
    
    public function __construct()
    {
        global $wpdb;
        $this->table = $wpdb->prefix . 'survey_sphere_surveys';
    }
    
    /**
     * @throws DatabaseException
     */
    public function create(array $data): ?Survey
    {
        global $wpdb;
        
        // Generate public ID
        $data['public_id'] = $this->generateNanoid();
        
        $result = $wpdb->insert(
            $this->table,
            [
                'public_id' => $data['public_id'],
                'name' => sanitize_text_field($data['name']),
                'description' => sanitize_textarea_field($data['description'] ?? ''),
                'chart_type' => sanitize_text_field($data['chart_type'] ?? 'polar'),
                'is_active' => (int)($data['is_active'] ?? true),
                'settings' => isset($data['settings']) ? wp_json_encode($data['settings']) : null,
            ],
            ['%s', '%s', '%s', '%s', '%d', '%s']
        );
        
        if ($result === false) {
            throw new DatabaseException('Failed to create survey: ' . $wpdb->last_error);
        }
        
        return $this->findById((int)$wpdb->insert_id);
    }
    
    /**
     * @throws DatabaseException
     */
    public function update(int $id, array $data): ?Survey
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
        
        if (isset($data['chart_type'])) {
            $updateData['chart_type'] = sanitize_text_field($data['chart_type']);
            $formats[] = '%s';
        }
        
        if (isset($data['is_active'])) {
            $updateData['is_active'] = (int)$data['is_active'];
            $formats[] = '%d';
        }
        
        if (isset($data['settings'])) {
            $updateData['settings'] = wp_json_encode($data['settings']);
            $formats[] = '%s';
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
            throw new DatabaseException('Failed to update survey: ' . $wpdb->last_error);
        }
        
        return $this->findById($id);
    }
    
    public function findById(int $id): ?Survey
    {
        global $wpdb;
        
        $row = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$this->table} WHERE id = %d", $id),
            ARRAY_A
        );
        
        return $row ? Survey::fromArray($row) : null;
    }
    
    public function findByPublicId(string $publicId): ?Survey
    {
        global $wpdb;
        
        $row = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$this->table} WHERE public_id = %s", $publicId),
            ARRAY_A
        );
        
        return $row ? Survey::fromArray($row) : null;
    }
    
    public function findAll(bool $activeOnly = false): array
    {
        global $wpdb;
        
        $sql = "SELECT * FROM {$this->table}";
        
        if ($activeOnly) {
            $sql .= " WHERE is_active = 1";
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        $rows = $wpdb->get_results($sql, ARRAY_A);
        
        return array_map([Survey::class, 'fromArray'], $rows);
    }
    
    /**
     * @throws DatabaseException
     */
    public function delete(int $id): bool
    {
        global $wpdb;
        
        $result = $wpdb->delete(
            $this->table,
            ['id' => $id],
            ['%d']
        );
        
        if ($result === false) {
            throw new DatabaseException('Failed to delete survey: ' . $wpdb->last_error);
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