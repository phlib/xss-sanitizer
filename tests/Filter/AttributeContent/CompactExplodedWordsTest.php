<?php

declare(strict_types=1);

namespace Phlib\XssSanitizer\Test\Filter\AttributeContent;

use Phlib\XssSanitizer\Filter\AttributeContent\CompactExplodedWords;
use PHPUnit\Framework\TestCase;

/**
 * @package Phlib\XssSanitizer
 */
class CompactExplodedWordsTest extends TestCase
{
    /**
     * @dataProvider filterDataProvider
     */
    public function testFilter(string $original, string $expected): void
    {
        $actual = (new CompactExplodedWords())->filter($original);
        static::assertSame($expected, $actual);
    }

    public function filterDataProvider(): array
    {
        return [
            ['j a v a s c r i p t:alert(document.cookie);', 'javascript:alert(document.cookie);'],
            ['jav	ascript:alert(\'XSS\');', 'javascript:alert(\'XSS\');'],
            ['r e f r e s h', 'refresh'],

            // should not be affected
            ['javascriptor', 'javascriptor'],
        ];
    }
}
