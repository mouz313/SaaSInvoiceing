<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMNode;
use Exception;

class SvgSanitizer
{
    /**
     * Tags that should be completely removed from the SVG DOM along with their children.
     *
     * @var array<string>
     */
    protected static array $blockedTags = [
        'script',
        'iframe',
        'object',
        'embed',
        'foreignobject',
        'applet',
        'meta',
        'link',
        'form',
        'input',
        'button',
        'textarea',
        'select',
        'base',
    ];

    /**
     * Dangerous protocols in URI attributes.
     *
     * @var array<string>
     */
    protected static array $dangerousProtocols = [
        'javascript:',
        'vbscript:',
        'data:text/html',
        'data:text/javascript',
        'data:application/javascript',
        'data:application/x-javascript',
    ];

    /**
     * Sanitize SVG string content, removing scripts, event handlers, and XXE vulnerabilities.
     *
     * @throws Exception if SVG cannot be parsed or root element is not <svg>
     */
    public static function sanitize(string $svgContent): string
    {
        $trimmed = trim($svgContent);

        if (empty($trimmed)) {
            throw new Exception('Empty SVG content.');
        }

        // Quick check for XXE entity declaration in DOCTYPE
        if (preg_match('/<!ENTITY/i', $trimmed)) {
            throw new Exception('SVG contains forbidden entity declarations (XXE).');
        }

        // Strip any leading XML comments or prolog issues that could interfere
        $dom = new DOMDocument;
        // Prevent external entity loading and suppress libxml warnings
        $options = LIBXML_NONET | LIBXML_NOWARNING | LIBXML_NOERROR;
        if (defined('LIBXML_NOXMLDECL')) {
            $options |= LIBXML_NOXMLDECL;
        }

        $loaded = $dom->loadXML($trimmed, $options);
        if (! $loaded || ! $dom->documentElement) {
            throw new Exception('Invalid or malformed SVG document.');
        }

        if (strtolower($dom->documentElement->nodeName) !== 'svg') {
            throw new Exception('Root element of image must be <svg>.');
        }

        // Clean nodes recursively starting from root
        static::cleanNode($dom->documentElement);

        $sanitized = $dom->saveXML($dom->documentElement);

        if (! $sanitized) {
            throw new Exception('Failed to serialize sanitized SVG.');
        }

        return $sanitized;
    }

    /**
     * Clean a DOM node and its descendants.
     */
    protected static function cleanNode(DOMNode $node): void
    {
        if ($node->nodeType !== XML_ELEMENT_NODE) {
            return;
        }

        /** @var DOMElement $element */
        $element = $node;
        $tag = strtolower($element->nodeName);

        // Strip blocked tags immediately
        if (in_array($tag, static::$blockedTags, true)) {
            $element->parentNode?->removeChild($element);

            return;
        }

        // Inspect and sanitize attributes
        $attributesToRemove = [];

        if ($element->hasAttributes()) {
            foreach ($element->attributes as $attribute) {
                $attrName = strtolower($attribute->name);
                $attrValue = trim($attribute->value);

                // 1. Strip all inline JavaScript event handlers: onload, onerror, onclick, etc.
                if (str_starts_with($attrName, 'on')) {
                    $attributesToRemove[] = $attribute->name;

                    continue;
                }

                // 2. Check for script execution in href, xlink:href, src, etc.
                if (in_array($attrName, ['href', 'xlink:href', 'src', 'action', 'data'], true)) {
                    $normalizedVal = strtolower(preg_replace('/\s+/', '', $attrValue));

                    foreach (static::$dangerousProtocols as $protocol) {
                        if (str_starts_with($normalizedVal, $protocol)) {
                            $attributesToRemove[] = $attribute->name;
                            break;
                        }
                    }

                    // Check for external remote references in <use> tag
                    if ($tag === 'use' && (str_starts_with($normalizedVal, 'http://') || str_starts_with($normalizedVal, 'https://') || str_starts_with($normalizedVal, '//'))) {
                        $attributesToRemove[] = $attribute->name;
                    }
                }

                // 3. Inspect style attribute for expression() or javascript:
                if ($attrName === 'style') {
                    $loweredStyle = strtolower($attrValue);
                    if (str_contains($loweredStyle, 'expression(') || str_contains($loweredStyle, 'javascript:') || str_contains($loweredStyle, 'behavior:')) {
                        $attributesToRemove[] = $attribute->name;
                    }
                }
            }

            foreach ($attributesToRemove as $name) {
                $element->removeAttribute($name);
            }
        }

        // Sanitize <style> tags content if present
        if ($tag === 'style') {
            $styleContent = $element->textContent;
            $lowered = strtolower($styleContent);
            if (str_contains($lowered, 'expression(') || str_contains($lowered, 'javascript:') || str_contains($lowered, '@import')) {
                $element->parentNode?->removeChild($element);

                return;
            }
        }

        // Recurse child nodes
        $children = [];
        foreach ($element->childNodes as $child) {
            $children[] = $child;
        }

        foreach ($children as $child) {
            static::cleanNode($child);
        }
    }
}
