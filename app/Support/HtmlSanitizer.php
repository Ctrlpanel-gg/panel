<?php

namespace App\Support;

class HtmlSanitizer
{
    public static function sanitizeRichText(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $clean = strip_tags($value, '<p><br><strong><em><ul><ol><li><a>');

        $clean = preg_replace('/\s+on[a-z]+\s*=\s*(["\']).*?\1/iu', '', $clean) ?? $clean;
        $clean = preg_replace('/\s+on[a-z]+\s*=\s*[^\s>]+/iu', '', $clean) ?? $clean;
        $clean = preg_replace_callback(
            '/\s+href\s*=\s*(["\']?)([^"\'>\s]+)\1/iu',
            static function (array $matches): string {
                $url = trim($matches[2]);

                if (preg_match('/^(javascript|data):/iu', $url) === 1) {
                    return ' href="#"';
                }

                return sprintf(' href="%s"', e($url));
            },
            $clean
        ) ?? $clean;

        return trim($clean);
    }

    public static function sanitizeIconClass(?string $value): string
    {
        return trim((string) preg_replace('/[^A-Za-z0-9 _-]/', '', (string) $value));
    }
}
