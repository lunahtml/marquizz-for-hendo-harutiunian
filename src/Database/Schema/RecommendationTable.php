<?php
//src/Database/Schema/RecommendationTable.php
declare(strict_types=1);

namespace SurveySphere\Database\Schema;

final class RecommendationTable
{
    public static function getTableName(): string
    {
        global $wpdb;
        return $wpdb->prefix . 'survey_sphere_recommendations';
    }
    
    public static function create(): void
    {
        global $wpdb;
        
        $tableName = self::getTableName();
        $surveyTable = SurveyTable::getTableName();
        $segmentTable = SegmentTable::getTableName();
        $charsetCollate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS {$tableName} (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            public_id VARCHAR(21) NOT NULL UNIQUE,
            survey_id BIGINT UNSIGNED NOT NULL,
            segment_id BIGINT UNSIGNED NULL,
            min_score INT DEFAULT 0,
            max_score INT DEFAULT 100,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            action_text VARCHAR(255),
            action_url VARCHAR(500),
            order_index INT DEFAULT 0,
            is_active TINYINT(1) DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (survey_id) REFERENCES {$surveyTable}(id) ON DELETE CASCADE,
            FOREIGN KEY (segment_id) REFERENCES {$segmentTable}(id) ON DELETE CASCADE,
            INDEX idx_survey (survey_id),
            INDEX idx_segment (segment_id),
            INDEX idx_score (min_score, max_score)
        ) {$charsetCollate}";
        
        dbDelta($sql);
    }
}