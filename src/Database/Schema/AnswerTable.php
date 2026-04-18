<?php
//src/Database/Schema/AnswerTable.php
declare(strict_types=1);

namespace SurveySphere\Database\Schema;

final class AnswerTable
{
    public static function getTableName(): string
    {
        global $wpdb;
        return $wpdb->prefix . 'survey_sphere_answers';
    }
    
    public static function create(): void
    {
        global $wpdb;
        
        $tableName = self::getTableName();
        $attemptTable = AttemptTable::getTableName();
        $questionTable = QuestionTable::getTableName();
        $optionTable = OptionTable::getTableName();
        $charsetCollate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS {$tableName} (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            attempt_id BIGINT UNSIGNED NOT NULL,
            question_id BIGINT UNSIGNED NOT NULL,
            option_id BIGINT UNSIGNED NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (attempt_id) REFERENCES {$attemptTable}(id) ON DELETE CASCADE,
            FOREIGN KEY (question_id) REFERENCES {$questionTable}(id) ON DELETE CASCADE,
            FOREIGN KEY (option_id) REFERENCES {$optionTable}(id) ON DELETE CASCADE,
            UNIQUE KEY unique_answer (attempt_id, question_id),
            INDEX idx_attempt (attempt_id),
            INDEX idx_question (question_id)
        ) {$charsetCollate}";
        
        dbDelta($sql);
    }
}