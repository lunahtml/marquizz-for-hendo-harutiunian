<?php
//src/Database/Schema/Manager.php
declare(strict_types=1);

namespace SurveySphere\Database\Schema;

final class Manager
{
    private const VERSION = '1.0.0';
    
    public static function createAllTables(): void
    {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        
        SurveyTable::create();
        SegmentTable::create();
        QuestionTable::create();
        OptionTable::create();
        RespondentTable::create();
        AttemptTable::create();
        AnswerTable::create();
        SurveyQuestionTable::create(); 
        RecommendationTable::create();
        
        add_option('survey_sphere_db_version', self::VERSION);
    }
    
    public static function dropAllTables(): void
    {
        global $wpdb;
        
        $tables = [
            AnswerTable::getTableName(),
            AttemptTable::getTableName(),
            RespondentTable::getTableName(),
            OptionTable::getTableName(),
            QuestionTable::getTableName(),
            SegmentTable::getTableName(),
            SurveyTable::getTableName(),
        ];
        
        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS {$table}");
        }
    }
}