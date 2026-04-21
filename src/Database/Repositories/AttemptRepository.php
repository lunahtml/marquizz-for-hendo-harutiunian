<?php
//src/Database/Repositories/AttemptRepository.php
declare(strict_types=1);

namespace SurveySphere\Database\Repositories;

use SurveySphere\Database\Models\Attempt;
use SurveySphere\Exceptions\DatabaseException;

final class AttemptRepository
{
    private string $table;
    
    public function __construct()
    {
        global $wpdb;
        $this->table = $wpdb->prefix . 'survey_sphere_attempts';
    }
    
    public function create(array $data): ?Attempt
    {
        global $wpdb;
        
        $data['public_id'] = $this->generateNanoid();
        
        $result = $wpdb->insert(
            $this->table,
            [
                'public_id' => $data['public_id'],
                'survey_id' => (int)$data['survey_id'],
                'respondent_id' => isset($data['respondent_id']) ? (int)$data['respondent_id'] : null,
            ],
            ['%s', '%d', '%d']
        );
        
        if ($result === false) {
            throw new DatabaseException('Failed to create attempt: ' . $wpdb->last_error);
        }
        
        return $this->findById((int)$wpdb->insert_id);
    }
    
    public function findById(int $id): ?Attempt
    {
        global $wpdb;
        
        $row = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$this->table} WHERE id = %d", $id),
            ARRAY_A
        );
        
        return $row ? Attempt::fromArray($row) : null;
    }
    
    public function findByPublicId(string $publicId): ?Attempt
    {
        global $wpdb;
        
        $row = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$this->table} WHERE public_id = %s", $publicId),
            ARRAY_A
        );
        
        return $row ? Attempt::fromArray($row) : null;
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