<?php

declare(strict_types=1);

namespace Phlib\XssSanitizer\Test\Filter;

use Phlib\XssSanitizer\Filter\EscapeTags;
use PHPUnit\Framework\TestCase;

/**
 * @package Phlib\XssSanitizer
 */
class EscapeTagsTest extends TestCase
{
    /**
     * @dataProvider escapeTagsDataProvider
     */
    public function testEscapeTags(string $original, string $expected): void
    {
        $actual = (new EscapeTags('script'))->filter($original);
        static::assertEquals($expected, $actual);
    }

    public function escapeTagsDataProvider(): array
    {
        return [
            ['<body><script>alert(\'XSS\');</script></body>', '<body>&lt;script>alert(\'XSS\');&lt;/script></body>'],
            ['<body><scri<script>pt>alert(\'XSS\');<scri</script>pt></body>', '<body><scri&lt;script>pt>alert(\'XSS\');<scri&lt;/script>pt></body>'],
        ];
    }
}
