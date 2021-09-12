<?php

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
     * @param string $original
     * @param string $expected
     */
    public function testEscapeTags($original, $expected)
    {
        $actual = (new EscapeTags('script'))->filter($original);
        static::assertEquals($expected, $actual);
    }

    public function escapeTagsDataProvider()
    {
        return [
            ['<body><script>alert(\'XSS\');</script></body>', '<body>&lt;script>alert(\'XSS\');&lt;/script></body>'],
            ['<body><scri<script>pt>alert(\'XSS\');<scri</script>pt></body>', '<body><scri&lt;script>pt>alert(\'XSS\');<scri&lt;/script>pt></body>'],
        ];
    }
}
