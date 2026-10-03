<?php

declare(strict_types=1);

namespace KMark\Tests;

use KMark\BlockSplitter;
use PHPUnit\Framework\TestCase;

final class BlockSplitterTest extends TestCase
{
    public function test_an_unclosed_code_block_runs_to_the_end(): void
    {
        self::assertSame(["~~\na\n\nb"], (new BlockSplitter())->split("~~\na\n\nb"));
    }

    public function test_keeps_the_blank_lines_of_a_code_block(): void
    {
        self::assertSame(["~~\na\n\nb\n~~", 'C'], (new BlockSplitter())->split("~~\na\n\nb\n~~\nC"));
    }

    public function test_splits_on_the_blank_lines(): void
    {
        self::assertSame(["A\nB", 'C'], (new BlockSplitter())->split("A\nB\n\n\nC\n"));
    }

    public function test_starts_a_new_block_before_a_code_block(): void
    {
        self::assertSame(['A', "~~\nx\n~~"], (new BlockSplitter())->split("A\n~~\nx\n~~"));
    }
}
