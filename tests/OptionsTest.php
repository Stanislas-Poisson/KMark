<?php

declare(strict_types=1);

namespace KMark\Tests;

use Closure;
use InvalidArgumentException;
use KMark\Options;
use KMark\Tag;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class OptionsTest extends TestCase
{
    /**
     * @return iterable<string, array{Closure(): Options, string}>
     */
    public static function invalidProvider(): iterable
    {
        yield 'scheme in capitals' => [static fn (): Options => new Options(allowedSchemes: ['HTTP']), '"HTTP" is not a valid scheme'];

        yield 'unknown style' => [static fn (): Options => new Options(tags: ['strong' => new Tag('u')]), '"strong" is not a style'];

        yield 'bad element name' => [static fn (): Options => new Options(tags: ['italic' => new Tag('Span')]), '"Span" is not a valid element name'];

        yield 'bad class name' => [static fn (): Options => new Options(tags: ['italic' => new Tag('i', ['a b'])]), '"a b" is not a valid class name'];

        yield 'scheme with a colon' => [static fn (): Options => new Options(allowedSchemes: ['http:']), '"http:" is not a valid scheme'];
    }

    public function test_defaults(): void
    {
        $options = new Options();

        self::assertTrue($options->autoLinks);
        self::assertFalse($options->unsafeAllowRawHtml);
        self::assertSame(['http', 'https', 'mailto', 'tel', 'ftp'], $options->allowedSchemes);
        self::assertSame('b', $options->tag('bold')->name);
    }

    /**
     * @param Closure(): Options $create
     */
    #[DataProvider('invalidProvider')]
    public function test_rejects_an_invalid_setting(Closure $create, string $message): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($message);

        $create();
    }
}
