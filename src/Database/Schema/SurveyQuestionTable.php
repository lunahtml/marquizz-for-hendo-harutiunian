<?php
//src/Database/Schema/SurveyQuestionTable.php
declare(strict_types=1);

namespace SurveySphere\Database\Schema;

final class SurveyQuestionTable
{
    public static function getTableName(): string
    {
        global $wpdb;
        return $wpdb->prefix . 'survey_sphere_survey_questions';
    }
    
    public static function create(): void
    {
        global $wpdb;
        
        $tableName = self::getTableName();
        $surveyTable = SurveyTable::getTableName();
        $questionTable = QuestionTable::getTableName();
        $segmentTable = SegmentTable::getTableName();
        $charsetCollate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS {$tableName} (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            survey_id BIGINT UNSIGNED NOT NULL,
            question_id BIGINT UNSIGNED NOT NULL,
            segment_id BIGINT UNSIGNED NULL,
            order_index INT UNSIGNED DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (survey_id) REFERENCES {$surveyTable}(id) ON DELETE CASCADE,
            FOREIGN KEY (question_id) REFERENCES {$questionTable}(id) ON DELETE CASCADE,
            FOREIGN KEY (segment_id) REFERENCES {$segmentTable}(id) ON DELETE SET NULL,
            UNIQUE KEY unique_survey_question (survey_id, question_id),
            INDEX idx_survey (survey_id),
            INDEX idx_question (question_id),
            INDEX idx_segment (segment_id),
            INDEX idx_order (order_index)
        ) {$charsetCollate} COMMENT='Survey-Questions many-to-many relationship with segments';";
        
        dbDelta($sql);
    }
}