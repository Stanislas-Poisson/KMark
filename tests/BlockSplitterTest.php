<?php

declare(strict_types=1);

namespace KMark\Tests;

use KMark\BlockSplitter;
use PHPUnit\Framework\TestCase;

final class BlockSplitterTest extends TestCase
{
    public function testSplitsOnTheBlankLines(): void
    {
        self::assertSame(["A\nB", 'C'], (new BlockSplitter())->split("A\nB\n\n\nC\n"));
    }

    public function testKeepsTheBlankLinesOfACodeBlock(): void
    {
        self::assertSame(["~~\na\n\nb\n~~", 'C'], (new BlockSplitter())->split("~~\na\n\nb\n~~\nC"));
    }

    public function testStartsANewBlockBeforeACodeBlock(): void
    {
        self::assertSame(['A', "~~\nx\n~~"], (new BlockSplitter())->split("A\n~~\nx\n~~"));
    }

    public function testAnUnclosedCodeBlockRunsToTheEnd(): void
    {
        self::assertSame(["~~\na\n\nb"], (new BlockSplitter())->split("~~\na\n\nb"));
    }
}
