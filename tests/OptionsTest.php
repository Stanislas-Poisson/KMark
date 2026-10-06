<?php

declare(strict_types=1);

namespace KMark\Tests;

use Closure;
use InvalidArgumentException;
use KMark\Options;
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

        yield 'scheme with a colon' => [static fn (): Options => new Options(allowedSchemes: ['http:']), '"http:" is not a valid scheme'];
    }

    public function test_defaults(): void
    {
        $options = new Options();

        self::assertTrue($options->autoLinks);
        self::assertFalse($options->unsafeAllowRawHtml);
        self::assertSame(['http', 'https', 'mailto', 'tel', 'ftp'], $options->allowedSchemes);
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
