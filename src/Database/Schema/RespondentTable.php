<?php
//src/Database/Schema/RespondentTable.php
declare(strict_types=1);

namespace SurveySphere\Database\Schema;

final class RespondentTable
{
    public static function getTableName(): string
    {
        global $wpdb;
        return $wpdb->prefix . 'survey_sphere_respondents';
    }
    
    public static function create(): void
    {
        global $wpdb;
        
        $tableName = self::getTableName();
        $charsetCollate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS {$tableName} (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            public_id VARCHAR(21) NOT NULL UNIQUE,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE,
            phone VARCHAR(20) NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_email (email),
            INDEX idx_public (public_id)
        ) {$charsetCollate}";
        
        dbDelta($sql);
    }
}