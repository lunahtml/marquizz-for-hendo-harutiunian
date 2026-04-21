<?php
//src/Database/Schema/QuestionTable.php
declare(strict_types=1);

namespace SurveySphere\Database\Schema;

final class QuestionTable
{
    public static function getTableName(): string
    {
        global $wpdb;
        return $wpdb->prefix . 'survey_sphere_questions';
    }
    
    public static function create(): void
    {
        global $wpdb;
        
        $tableName = self::getTableName();
        $segmentTable = SegmentTable::getTableName();
        $charsetCollate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS {$tableName} (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            public_id VARCHAR(21) NOT NULL UNIQUE,
            segment_id BIGINT UNSIGNED NULL,
            text TEXT NOT NULL,
            type ENUM('radio', 'checkbox', 'true_false', 'text', 'rating') DEFAULT 'radio',
            order_index INT UNSIGNED DEFAULT 0,
            is_required TINYINT(1) DEFAULT 1,
            is_active TINYINT(1) DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (segment_id) REFERENCES {$segmentTable}(id) ON DELETE SET NULL,
            INDEX idx_segment (segment_id),
            INDEX idx_order (order_index),
            INDEX idx_public (public_id)
        ) {$charsetCollate} COMMENT='Questions storage';";
        
        dbDelta($sql);
    }
}