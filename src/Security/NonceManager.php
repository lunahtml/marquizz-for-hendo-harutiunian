<?php
//src/Security/NonceManager.php
declare(strict_types=1);

namespace SurveySphere\Security;

class NonceManager
{
    public static function verify(string $action, string $nonce): bool
    {
        return wp_verify_nonce($nonce, $action) !== false;
    }

    public static function create(string $action): string
    {
        return wp_create_nonce($action);
    }
}