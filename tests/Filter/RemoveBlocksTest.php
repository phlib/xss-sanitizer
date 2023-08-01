<?php

declare(strict_types=1);

namespace Phlib\XssSanitizer\Test\Filter;

use Phlib\XssSanitizer\Filter\RemoveBlocks;
use PHPUnit\Framework\TestCase;

/**
 * @package Phlib\XssSanitizer
 */
class RemoveBlocksTest extends TestCase
{
    /**
     * @dataProvider removeBlocksDataProvider
     */
    public function testRemoveBlocks(string $original, string $expected): void
    {
        $actual = (new RemoveBlocks('script'))->filter($original);
        static::assertSame($expected, $actual);
    }

    public function removeBlocksDataProvider(): array
    {
        return [
            [
                '<body><script>alert(\'XSS\');</script></body>',
                '<body></body>',
            ],
            [
                "<body><script>alert('XSS');\nalert('Multi line');</script></body>",
                '<body></body>',
            ],

            /**
             *  this shows why this filter should be used in conjunction with @see \Phlib\XssSanitizer\Filter\EscapeTags
             */
            [
                '<body><scri<script>pt>alert(\'XSS\');<scri</script>pt></body>',
                '<body><script></body>',
            ],
            [
                '<body><script>var x = "</script>";alert(\'XSS\');</script></body>',
                '<body>";alert(\'XSS\');</script></body>',
            ],
        ];
    }
}
