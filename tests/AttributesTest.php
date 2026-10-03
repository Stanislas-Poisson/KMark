<?php

declare(strict_types=1);

namespace KMark\Tests;

use KMark\Attributes;
use PHPUnit\Framework\TestCase;

final class AttributesTest extends TestCase
{
    public function testExtractsTheBlockAtTheEnd(): void
    {
        [$text, $attributes] = Attributes::extract('Title {#main .a .b}');

        self::assertSame('Title', $text);
        self::assertSame('main', $attributes->id);
        self::assertSame(['a', 'b'], $attributes->classes);
        self::assertSame(' id="main" class="a b"', $attributes->render());
    }

    public function testLeavesTheTextThatHasNoValidBlock(): void
    {
        foreach (['Title', 'Title {}', 'Title {x}', 'Title {#a b}', 'Title {#}', '{.a} Title'] as $text) {
            [$remaining, $attributes] = Attributes::extract($text);

            self::assertSame($text, $remaining);
            self::assertSame('', $attributes->render());
        }
    }

    public function testMergeKeepsTheFirstIdAndGathersTheClasses(): void
    {
        $merged = (new Attributes('a', ['x']))->merge(new Attributes('b', ['y']));

        self::assertSame(' id="a" class="x y"', $merged->render());
        self::assertSame(' id="b"', (new Attributes())->merge(new Attributes('b'))->render());
    }
}
