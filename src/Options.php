<?php

declare(strict_types=1);

namespace KMark;

/**
 * The settings of a conversion. The object is immutable: use the named arguments to change a setting.
 *
 *     new Options(autoLinks: false, styleClasses: ['*' => 'strong']);
 */
final readonly class Options
{
    /**
     * The CSS class of each style, by marker.
     */
    public const DEFAULT_STYLE_CLASSES = ['*' => 'b', '-' => 'i', '_' => 'u', '~' => 'd'];

    /**
     * @param bool                  $autoLinks           turn the bare "http://" and "https://" URLs into links
     * @param bool                  $unsafeAllowRawHtml  keep the HTML written in the text instead of escaping it:
     *                                                   never use it on a text you do not trust, it allows scripts
     * @param array<string, string> $styleClasses        the CSS class of each style marker ("*", "-", "_" and "~");
     *                                                   a marker that is left out is not a style
     * @param list<string>          $allowedSchemes      the schemes that a link or an image can have, in lowercase;
     *                                                   a relative URL is always allowed
     *
     * @throws \InvalidArgumentException when a style marker, a class or a scheme is not valid
     */
    public function __construct(
        public bool $autoLinks = true,
        public bool $unsafeAllowRawHtml = false,
        public array $styleClasses = self::DEFAULT_STYLE_CLASSES,
        public array $allowedSchemes = ['http', 'https', 'mailto', 'tel', 'ftp'],
    ) {
        foreach ($styleClasses as $marker => $class) {
            if (!array_key_exists($marker, self::DEFAULT_STYLE_CLASSES)) {
                throw new \InvalidArgumentException(sprintf('"%s" is not a style marker: use "*", "-", "_" or "~".', $marker));
            }

            if (1 !== preg_match('/^[\w-]+$/', $class)) {
                throw new \InvalidArgumentException(sprintf('"%s" is not a valid CSS class: use letters, digits, "_" and "-".', $class));
            }
        }

        foreach ($allowedSchemes as $scheme) {
            if (1 !== preg_match('/^[a-z][a-z0-9+.\-]*$/', $scheme)) {
                throw new \InvalidArgumentException(sprintf('"%s" is not a valid scheme: use lowercase letters, digits, "+", "." and "-".', $scheme));
            }
        }
    }
}
