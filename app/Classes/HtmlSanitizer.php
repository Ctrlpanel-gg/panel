<?php

namespace App\Classes;

use DOMDocument;
use DOMElement;
use DOMXPath;

/**
 * Sanitizes untrusted HTML that is later rendered unescaped ({!! !!}).
 *
 * Content is restricted to a configurable allow-list of tags and attributes.
 * Everything else (scripts, event handlers, iframes, object/embed, style
 * injection, unsafe URL schemes) is stripped, and unbalanced markup is
 * repaired into a valid document fragment.
 *
 * Note: This thing was vibecoded. Why? I (MrWeez) don't want to introduce
 * a new Composer dependency. We already had problems with captcha dependencies
 * that made it harder for us to migrate to a newer Laravel. At least this
 * thing works, and sanitizes HTML before saving/rendering it.
 */
class HtmlSanitizer
{
    /**
     * Tag allow-list, mapped to the attributes permitted on each tag.
     */
    public const ALLOWED_TAGS = [
        'h1' => ['style'],
        'h2' => ['style'],
        'h3' => ['style'],
        'h4' => ['style'],
        'h5' => ['style'],
        'h6' => ['style'],
        'p' => ['style'],
        'br' => [],
        'strong' => [],
        'b' => [],
        'em' => [],
        'i' => [],
        'u' => [],
        's' => [],
        'blockquote' => ['style'],
        'pre' => ['style'],
        'code' => [],
        'ul' => ['style'],
        'ol' => ['style'],
        'li' => ['style'],
        'hr' => [],
        'sup' => [],
        'sub' => [],
        'a' => ['href', 'title', 'target'],
        'img' => ['src', 'alt', 'title', 'width', 'height', 'style'],
        'span' => ['style'],
        'div' => ['style'],
        'table' => ['summary', 'width', 'style'],
        'thead' => [],
        'tbody' => [],
        'tr' => ['style'],
        'th' => ['colspan', 'rowspan', 'style'],
        'td' => ['colspan', 'rowspan', 'style'],
    ];

    public const ALLOWED_SCHEMES = ['http', 'https', 'mailto'];

    public const ALLOWED_TARGETS = ['_blank', '_self', '_top', '_parent'];

    /**
     * CSS properties permitted inside inline style attributes.
     */
    public const ALLOWED_CSS_PROPERTIES = [
        'font',
        'font-size',
        'font-weight',
        'font-style',
        'font-family',
        'text-decoration',
        'padding',
        'padding-left',
        'padding-right',
        'color',
        'background-color',
        'text-align',
        'margin',
        'margin-left',
        'margin-right',
        'line-height',
    ];

    /**
     * Tags whose entire subtree is dropped (not unwrapped) when encountered.
     */
    public const REMOVE_TAGS = ['script', 'style', 'iframe', 'object', 'embed', 'meta', 'link', 'base', 'form', 'input', 'button', 'textarea', 'select'];

    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(
        protected array $config = [],
    ) {
    }

    /**
     * @param  string  $html
     * @return string
     */
    public function clean(string $html): string
    {
        $allowedTags = $this->config['allowed_tags'] ?? self::ALLOWED_TAGS;
        $removeTags = $this->config['remove_tags'] ?? self::REMOVE_TAGS;
        $allowedSchemes = $this->config['allowed_schemes'] ?? self::ALLOWED_SCHEMES;
        $allowedTargets = $this->config['allowed_targets'] ?? self::ALLOWED_TARGETS;
        $allowedCss = $this->config['allowed_css_properties'] ?? self::ALLOWED_CSS_PROPERTIES;

        $dom = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);

        // Load as a full document so a plain text fragment (no root element)
        // is parsed reliably and a <body> is always present.
        $dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_HTML_NODEFDTD);

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $xpath = new DOMXPath($dom);

        // Snapshot the queries so iteration is safe regardless of whether the
        // DOMNodeList behaves as a live or static collection when nodes are
        // removed/unwrapped mid-loop.
        foreach ($removeTags as $tag) {
            foreach (iterator_to_array($xpath->query('//body//' . $tag)) as $node) {
                if ($node->parentNode) {
                    $node->parentNode->removeChild($node);
                }
            }
        }

        foreach (iterator_to_array($xpath->query('//body//*')) as $node) {
            if (!$node instanceof DOMElement) {
                continue;
            }

            $tag = strtolower($node->nodeName);

            if (!isset($allowedTags[$tag])) {
                $this->unwrap($node);
                continue;
            }

            $this->stripUnsafeAttributes($node, $tag, $allowedTags, $allowedSchemes, $allowedTargets, $allowedCss);
        }

        $body = $dom->getElementsByTagName('body')->item(0);
        if (!$body) {
            return '';
        }

        $fragment = '';
        foreach ($body->childNodes as $child) {
            $fragment .= $dom->saveHTML($child);
        }

        return $fragment;
    }

    /**
     * Remove any attribute that is not in the allow-list for the tag or whose
     * value fails the scheme/target/style checks.
     *
     * @param  array<string, mixed>  $allowedTags
     * @param  array<int, string>  $allowedSchemes
     * @param  array<int, string>  $allowedTargets
     * @param  array<int, string>  $allowedCss
     */
    private function stripUnsafeAttributes(DOMElement $node, string $tag, array $allowedTags, array $allowedSchemes, array $allowedTargets, array $allowedCss): void
    {
        $allowed = $allowedTags[$tag];
        $attributes = iterator_to_array($node->attributes);

        foreach ($attributes as $attribute) {
            $name = strtolower($attribute->nodeName);
            $value = $attribute->nodeValue;

            if (!in_array($name, $allowed, true)) {
                $node->removeAttribute($attribute->nodeName);
                continue;
            }

            switch ($name) {
                case 'href':
                case 'src':
                    if (!$this->isSafeUrl($value, $allowedSchemes, $name)) {
                        $node->removeAttribute($attribute->nodeName);
                    }
                    break;
                case 'target':
                    if (!in_array($value, $allowedTargets, true)) {
                        $node->removeAttribute($attribute->nodeName);
                    }
                    break;
                case 'style':
                    $safeCss = $this->sanitizeCss($value, $allowedCss);
                    if ($safeCss === '') {
                        $node->removeAttribute($attribute->nodeName);
                    } else {
                        $node->setAttribute('style', $safeCss);
                    }
                    break;
            }
        }

        // Mitigate reverse tabnabbing: a link that opens in a new tab must not
        // expose window.opener to the destination page.
        if ($tag === 'a' && $node->hasAttribute('target') && $node->getAttribute('target') === '_blank') {
            $node->setAttribute('rel', 'noopener noreferrer');
        }
    }

    /**
     * Replace an element with its child nodes (keeps e.g. the text of an
     * <em> tag even though <em> itself is not in the allow-list).
     */
    private function unwrap(DOMElement $node): void
    {
        $parent = $node->parentNode;
        if (!$parent) {
            return;
        }

        while ($node->firstChild) {
            $parent->insertBefore($node->firstChild, $node);
        }

        $parent->removeChild($node);
    }

    /**
     * Only the configured URL schemes are allowed. ASCII tab/CR/LF are stripped
     * from the whole string first (as the browser URL parser does), relative
     * URLs (no scheme) and anchors are allowed, and protocol-relative URLs
     * (//host) are rejected.
     *
     * @param  array<int, string>  $allowedSchemes
     * @param  string  $attribute  'href' or 'src'
     */
    private function isSafeUrl(string $url, array $allowedSchemes, string $attribute): bool
    {
        // Strip ASCII tab/CR/LF anywhere in the string, not just at the edges.
        // Browsers do this before parsing the scheme, so java<tab>script: must
        // be treated as javascript: here too.
        $url = preg_replace('/[\t\r\n]/', '', $url);
        $url = trim($url);

        if ($url === '' || str_starts_with($url, '#')) {
            return true;
        }

        // Protocol-relative URLs (//host/path): no explicit scheme is present
        // but the browser still fetches an external host.
        if (str_starts_with($url, '//')) {
            return false;
        }

        // Paste-screenshot images from TinyMCE/Summernote are inline base64
        // data URIs. Allow only raster image mime types on src, never as href.
        if ($attribute === 'src'
            && preg_match('/^data:image\/(png|jpe?g|gif|webp);base64,/i', $url)) {
            return true;
        }

        // Validate any explicit scheme explicitly instead of relying on
        // parse_url() which returns null for malformed/obfuscated schemes.
        if (preg_match('/^([a-z][a-z0-9+.\-]*):/i', $url, $matches)) {
            return in_array(strtolower($matches[1]), $allowedSchemes, true);
        }

        // No scheme at all -> genuinely relative URL.
        return true;
    }

    /**
     * Drop every CSS declaration that isn't on the allow-list or whose value
     * contains dangerous tokens; keep the rest.
     *
     * @param  array<int, string>  $allowedCss
     */
    private function sanitizeCss(string $css, array $allowedCss): string
    {
        $kept = [];
        foreach (explode(';', $css) as $declaration) {
            $parts = explode(':', $declaration, 2);
            if (count($parts) !== 2) {
                continue;
            }

            $property = strtolower(trim($parts[0]));
            $value = trim($parts[1]);

            if ($value === '' || !in_array($property, $allowedCss, true)) {
                continue;
            }

            // Whitelist the value: only hex colors, numbers/units, plain
            // keywords, spaces, quotes, commas, dots, percent and dashes.
            // No parentheses, slashes or backslashes: CSS functions
            // (url(), expression(), calc(), var()) and escape sequences
            // cannot be expressed, so neither /* comment */ obfuscation nor
            // \xx unicode escapes can smuggle a dangerous value.
            if (!preg_match('/^[a-z0-9#%.,\'\"\-:\s]*$/i', $value)) {
                continue;
            }

            $kept[] = $property . ':' . $value;
        }

        return implode(';', $kept);
    }
}
