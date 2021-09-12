<?php

declare(strict_types=1);

namespace Phlib\XssSanitizer\Test\Filter\AttributeContent;

use Phlib\XssSanitizer\Filter\AttributeContent\DecodeUtf8;
use PHPUnit\Framework\TestCase;

/**
 * @package Phlib\XssSanitizer
 */
class DecodeUtf8Test extends TestCase
{
    /**
     * @dataProvider decodeDataProvider
     */
    public function testDecode(string $original, string $expected): void
    {
        $actual = (new DecodeUtf8())->filter($original);
        static::assertEquals($expected, $actual);
    }

    public function decodeDataProvider(): array
    {
        return [
            ['xx \u006a xx', 'xx j xx'],
            ['xx \u0061 xx', 'xx a xx'],
            ['xx \u0076 xx', 'xx v xx'],
            ['xx \u0073 xx', 'xx s xx'],
            ['xx \u003A xx', 'xx : xx'],
        ];
    }
}
