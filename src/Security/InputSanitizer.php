<?php
//src/Security/InputSanitizer.php
declare(strict_types=1);

namespace SurveySphere\Security;

final class InputSanitizer
{
    public static function text(string $value): string
    {
        return sanitize_text_field($value);
    }

    public static function html(string $value): string
    {
        return wp_kses_post($value);
    }

    public static function email(string $value): string
    {
        return sanitize_email($value);
    }

    public static function url(string $value): string
    {
        return esc_url_raw($value);
    }

    public static function key(string $value): string
    {
        return sanitize_key($value);
    }

    public static function integer($value): int
    {
        return (int) $value;
    }

    public static function float($value): float
    {
        return (float) $value;
    }

    public static function boolean($value): bool
    {
        return (bool) $value;
    }

    public static function array(array $value, string $type = 'text'): array
    {
        return array_map([self::class, $type], $value);
    }
}