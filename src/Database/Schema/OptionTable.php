<?php
//src/Database/Schema/OptionTable.php
declare(strict_types=1);

namespace SurveySphere\Database\Schema;

final class OptionTable
{
    public static function getTableName(): string
    {
        global $wpdb;
        return $wpdb->prefix . 'survey_sphere_options';
    }
    
    public static function create(): void
    {
        global $wpdb;
        
        $tableName = self::getTableName();
        $questionTable = QuestionTable::getTableName();
        $charsetCollate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS {$tableName} (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            public_id VARCHAR(21) NOT NULL UNIQUE,
            question_id BIGINT UNSIGNED NOT NULL,
            text TEXT NOT NULL,
            score DECIMAL(5,2) NOT NULL DEFAULT 0.00,
            order_index INT UNSIGNED DEFAULT 0,
            recommendation_text TEXT NULL,
            recommendation_level ENUM('low', 'medium', 'high', 'critical') NULL,
            is_active TINYINT(1) DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (question_id) REFERENCES {$questionTable}(id) ON DELETE CASCADE,
            INDEX idx_question (question_id),
            INDEX idx_score (score),
            INDEX idx_order (order_index)
        ) {$charsetCollate} COMMENT='Answer options storage';";
        
        dbDelta($sql);
    }
}