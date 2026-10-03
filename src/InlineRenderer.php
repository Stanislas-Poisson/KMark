<?php

declare(strict_types=1);

namespace KMark;

/**
 * Escapes a text, then converts its links and its images.
 *
 * @internal
 */
final class InlineRenderer
{
    private const ALLOWED_SCHEMES = ['http', 'https', 'mailto', 'tel', 'ftp'];

    public function render(string $text): string
    {
        $text = htmlspecialchars($text, ENT_NOQUOTES | ENT_SUBSTITUTE);
        $text = preg_replace_callback('/\[([^\]]*)\]:\(([^)]*)\)/', $this->link(...), $text) ?? $text;

        return preg_replace_callback('/!\[([^\]]*)\]\(([^)]*)\)/', $this->image(...), $text) ?? $text;
    }

    /**
     * @param array<int|string, string> $match
     */
    private function link(array $match): string
    {
        [$target, $attributes] = Attributes::extract($match[2]);
        $title = '';

        if (1 === preg_match('/^(.*?)\s*"(.*)"$/s', $target, $parts)) {
            $target = $parts[1];
            $title = trim($parts[2]);
        }

        $url = trim($target);

        if (!$this->isSafeUrl($url)) {
            return $match[1];
        }

        $html = '<a href="' . $this->quote($url) . '"';

        if ('' !== $title) {
            $html .= ' title="' . $this->quote($title) . '"';
        }

        return $html . $attributes->render() . '>' . $match[1] . '</a>';
    }

    /**
     * @param array<int|string, string> $match
     */
    private function image(array $match): string
    {
        [$target, $attributes] = Attributes::extract($match[2]);
        $url = trim($target);

        if (!$this->isSafeUrl($url)) {
            return $match[1];
        }

        return '<img src="' . $this->quote($url) . '" alt="' . $this->quote($match[1]) . '"' . $attributes->render() . '>';
    }

    /**
     * Accepts the relative URLs and the ones with a known scheme, so that
     * "javascript:" and "data:" never reach an attribute.
     */
    private function isSafeUrl(string $url): bool
    {
        $compact = preg_replace('/[\x00-\x20]+/', '', $url) ?? '';

        if (1 !== preg_match('/^([a-z][a-z0-9+.\-]*):/i', $compact, $scheme)) {
            return true;
        }

        return in_array(strtolower($scheme[1]), self::ALLOWED_SCHEMES, true);
    }

    /**
     * The text is already escaped, only the double quote can end an attribute.
     */
    private function quote(string $value): string
    {
        return str_replace('"', '&quot;', $value);
    }
}
