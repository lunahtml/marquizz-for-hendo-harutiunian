<?php
//src/Security/CapabilityManager.php

declare(strict_types=1);

namespace SurveySphere\Security;

final class CapabilityManager
{
    public static function addCapabilities(): void
    {
        $admin = get_role('administrator');
        
        $capabilities = [
            'manage_survey_sphere',
            'create_surveys',
            'edit_surveys',
            'delete_surveys',
            'view_survey_results',
            'export_survey_results',
            'manage_survey_settings',
        ];
        
        foreach ($capabilities as $cap) {
            $admin->add_cap($cap);
        }
    }
    
    public static function removeCapabilities(): void
    {
        $admin = get_role('administrator');
        
        $capabilities = [
            'manage_survey_sphere',
            'create_surveys',
            'edit_surveys',
            'delete_surveys',
            'view_survey_results',
            'export_survey_results',
            'manage_survey_settings',
        ];
        
        foreach ($capabilities as $cap) {
            $admin->remove_cap($cap);
        }
    }
}