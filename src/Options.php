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
     * @param bool         $autoLinks          turn the bare "http://" and "https://" URLs into links
     * @param bool         $unsafeAllowRawHtml keep the HTML written in the text instead of escaping it:
     *                                         never use it on a text you do not trust, it allows scripts
     * @param list<string> $allowedSchemes     the schemes that a link or an image can have, in lowercase;
     *                                         a relative URL is always allowed
     *
     * @throws InvalidArgumentException when a scheme is not valid
     */
    public function __construct(
        public bool $autoLinks = true,
        public bool $unsafeAllowRawHtml = false,
        public array $allowedSchemes = ['http', 'https', 'mailto', 'tel', 'ftp'],
    ) {
        $this->assertSchemes($allowedSchemes);
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
}
