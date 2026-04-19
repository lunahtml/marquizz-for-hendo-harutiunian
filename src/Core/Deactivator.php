<?php
//src/Core/Deactivator.php
declare(strict_types=1);

namespace SurveySphere\Core;

class Deactivator
{
    public static function deactivate(): void
    {
        // Очищаем запланированные кроны (если будут)
        wp_clear_scheduled_hook('survey_sphere_daily_cleanup');
        
        // Очищаем кэш реврайтов
        flush_rewrite_rules();
        
        // Удаляем transient-кэши (если будут)
        delete_transient('survey_sphere_cache');
    }
}