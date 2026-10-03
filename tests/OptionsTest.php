<?php

declare(strict_types=1);

namespace KMark\Tests;

use KMark\Options;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class OptionsTest extends TestCase
{
    public function testDefaults(): void
    {
        $options = new Options();

        self::assertTrue($options->autoLinks);
        self::assertFalse($options->unsafeAllowRawHtml);
        self::assertSame(['*' => 'b', '-' => 'i', '_' => 'u', '~' => 'd'], $options->styleClasses);
        self::assertSame(['http', 'https', 'mailto', 'tel', 'ftp'], $options->allowedSchemes);
    }

    /**
     * @return iterable<string, array{\Closure(): Options, string}>
     */
    public static function invalidProvider(): iterable
    {
        yield 'unknown marker' => [static fn (): Options => new Options(styleClasses: ['/' => 'd']), '"/" is not a style marker'];
        yield 'class with a space' => [static fn (): Options => new Options(styleClasses: ['*' => 'a b']), '"a b" is not a valid CSS class'];
        yield 'empty class' => [static fn (): Options => new Options(styleClasses: ['*' => '']), '"" is not a valid CSS class'];
        yield 'scheme in capitals' => [static fn (): Options => new Options(allowedSchemes: ['HTTP']), '"HTTP" is not a valid scheme'];
        yield 'scheme with a colon' => [static fn (): Options => new Options(allowedSchemes: ['http:']), '"http:" is not a valid scheme'];
    }

    /**
     * @param \Closure(): Options $create
     */
    #[DataProvider('invalidProvider')]
    public function testRejectsAnInvalidSetting(\Closure $create, string $message): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($message);

        $create();
    }
}
