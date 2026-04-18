<?php
//src/Database/Schema/SurveyTable.php
declare(strict_types=1);

namespace SurveySphere\Database\Schema;

final class SurveyTable
{
    public static function getTableName(): string
    {
        global $wpdb;
        return $wpdb->prefix . 'survey_sphere_surveys';
    }
    
    public static function create(): void
    {
        global $wpdb;
        
        $tableName = self::getTableName();
        $charsetCollate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS {$tableName} (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            public_id VARCHAR(21) NOT NULL UNIQUE COMMENT 'Nanoid for public access',
            name VARCHAR(255) NOT NULL,
            description TEXT,
            chart_type ENUM('polarArea', 'radar', 'bar', 'doughnut') DEFAULT 'polarArea',
            is_active TINYINT(1) DEFAULT 1,
            settings JSON DEFAULT NULL COMMENT 'Additional settings',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_public_id (public_id),
            INDEX idx_active (is_active),
            INDEX idx_created (created_at)
        ) {$charsetCollate} COMMENT='Surveys storage';";
        
        dbDelta($sql);
    }
}