<?php
//src/Database/Repositories/RespondentRepository.php
declare(strict_types=1);

namespace SurveySphere\Database\Repositories;

use SurveySphere\Database\Models\Respondent;
use SurveySphere\Exceptions\DatabaseException;

final class RespondentRepository
{
    private string $table;
    
    public function __construct()
    {
        global $wpdb;
        $this->table = $wpdb->prefix . 'survey_sphere_respondents';
    }
    
    public function findByEmail(string $email): ?Respondent
    {
        global $wpdb;
        
        $row = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$this->table} WHERE email = %s", $email),
            ARRAY_A
        );
        
        return $row ? Respondent::fromArray($row) : null;
    }
    
    public function create(array $data): ?Respondent
    {
        global $wpdb;
        
        $data['public_id'] = $this->generateNanoid();
        
        $result = $wpdb->insert(
            $this->table,
            [
                'public_id' => $data['public_id'],
                'name' => sanitize_text_field($data['name'] ?? ''),
                'email' => sanitize_email($data['email']),
                'phone' => sanitize_text_field($data['phone'] ?? null),
            ],
            ['%s', '%s', '%s', '%s']
        );
        
        if ($result === false) {
            throw new DatabaseException('Failed to create respondent: ' . $wpdb->last_error);
        }
        
        return $this->findById((int)$wpdb->insert_id);
    }
    
    public function findById(int $id): ?Respondent
    {
        global $wpdb;
        
        $row = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$this->table} WHERE id = %d", $id),
            ARRAY_A
        );
        
        return $row ? Respondent::fromArray($row) : null;
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