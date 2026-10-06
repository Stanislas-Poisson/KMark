<?php

declare(strict_types=1);

namespace KMark;

use InvalidArgumentException;

/**
 * The settings of a conversion. The object is immutable: use the named arguments to change a setting.
 *
 *     new Options(autoLinks: false, allowedSchemes: ['https']);
 */
final readonly class Options
{
    /**
     * @param bool               $autoLinks          turn the bare "http://" and "https://" URLs into links
     * @param bool               $unsafeAllowRawHtml keep the HTML written in the text instead of escaping it:
     *                                               never use it on a text you do not trust, it allows scripts
     * @param list<string>       $allowedSchemes     the schemes that a link or an image can have, in lowercase;
     *                                               a relative URL is always allowed
     * @param array<string, Tag> $tags               the element that writes a style, by style: "strong" (default
     *                                               <strong>), "em" (<em>) and "del" (<del>)
     *
     * @throws InvalidArgumentException when a scheme or a style is not valid
     */
    public function __construct(
        public bool $autoLinks = true,
        public bool $unsafeAllowRawHtml = false,
        public array $allowedSchemes = ['http', 'https', 'mailto', 'tel', 'ftp'],
        public array $tags = [],
    ) {
        $this->assertSchemes($allowedSchemes);
        $this->assertStyles($tags);
    }

    /**
     * The element of a style: the one that was set, or the default.
     */
    public function tag(string $style): Tag
    {
        return $this->tags[$style] ?? new Tag($style);
    }

    /**
     * @param list<string> $allowedSchemes
     */
    private function assertSchemes(array $allowedSchemes): void
    {
        foreach ($allowedSchemes as $allowedScheme) {
            if (1 !== preg_match('/^[a-z][a-z0-9+.\-]*$/', $allowedScheme)) {
                throw new InvalidArgumentException(sprintf(
                    '"%s" is not a valid scheme: use lowercase letters, digits, "+", "." and "-".',
                    $allowedScheme,
                ));
            }
        }
    }

    /**
     * @param array<string, Tag> $tags
     */
    private function assertStyles(array $tags): void
    {
        foreach (array_keys($tags) as $style) {
            if (! in_array($style, ['strong', 'em', 'del'], true)) {
                throw new InvalidArgumentException(
                    sprintf('"%s" is not a style: use "strong", "em" or "del".', $style),
                );
            }
        }
    }
}
