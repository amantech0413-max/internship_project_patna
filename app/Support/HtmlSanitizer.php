<?php

namespace App\Support;

class HtmlSanitizer
{
    /** @var string */
    private const ALLOWED_TAGS = '<p><br><strong><b><em><i><u><s><h1><h2><h3><h4><h5><h6><ul><ol><li><a><img><table><thead><tbody><tfoot><tr><th><td><blockquote><pre><code><span><div><hr><sub><sup><video><source>';

    public static function clean(?string $html): ?string
    {
        if ($html === null) {
            return null;
        }

        $html = trim($html);
        if ($html === '') {
            return null;
        }

        $html = strip_tags($html, self::ALLOWED_TAGS);
        $html = preg_replace('/\s(on\w+|style|class)\s*=\s*("|\').*?\2/i', '', $html) ?? $html;
        $html = preg_replace('/javascript\s*:/i', '', $html) ?? $html;
        $html = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $html) ?? $html;

        return $html;
    }
}
