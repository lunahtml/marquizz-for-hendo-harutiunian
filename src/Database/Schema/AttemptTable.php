<?php
//src/Database/Schema/AttemptTable.php

declare(strict_types=1);

namespace SurveySphere\Database\Schema;

final class AttemptTable
{
    public static function getTableName(): string
    {
        global $wpdb;
        return $wpdb->prefix . 'survey_sphere_attempts';
    }
    
    public static function create(): void
    {
        global $wpdb;
        
        $tableName = self::getTableName();
        $surveyTable = SurveyTable::getTableName();
        $respondentTable = RespondentTable::getTableName();
        $charsetCollate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS {$tableName} (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            public_id VARCHAR(21) NOT NULL UNIQUE,
            survey_id BIGINT UNSIGNED NOT NULL,
            respondent_id BIGINT UNSIGNED NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (survey_id) REFERENCES {$surveyTable}(id) ON DELETE CASCADE,
            FOREIGN KEY (respondent_id) REFERENCES {$respondentTable}(id) ON DELETE SET NULL,
            INDEX idx_survey (survey_id),
            INDEX idx_respondent (respondent_id)
        ) {$charsetCollate}";
        
        dbDelta($sql);
    }
}