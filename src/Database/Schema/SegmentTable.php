<?php
//src/Database/Schema/SegmentTable.php
declare(strict_types=1);

namespace SurveySphere\Database\Schema;

final class SegmentTable
{
    public static function getTableName(): string
    {
        global $wpdb;
        return $wpdb->prefix . 'survey_sphere_segments';
    }
    
    public static function create(): void
    {
        global $wpdb;
        
        $tableName = self::getTableName();
        $surveyTable = SurveyTable::getTableName();
        $charsetCollate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS {$tableName} (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            public_id VARCHAR(21) NOT NULL UNIQUE,
            survey_id BIGINT UNSIGNED NOT NULL,
            name VARCHAR(255) NOT NULL,
            description TEXT NULL,
            icon VARCHAR(10) NULL,
            color VARCHAR(20) DEFAULT '#36A2EB',
            order_index INT UNSIGNED DEFAULT 0,
            is_active TINYINT(1) DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (survey_id) REFERENCES {$surveyTable}(id) ON DELETE CASCADE,
            INDEX idx_survey (survey_id),
            INDEX idx_order (order_index),
            INDEX idx_active (is_active)
        ) {$charsetCollate} COMMENT='Survey segments/groups';";
        
        dbDelta($sql);
    }
}