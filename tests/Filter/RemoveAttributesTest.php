<?php

declare(strict_types=1);

namespace Phlib\XssSanitizer\Test\Filter;

use Phlib\XssSanitizer\Filter\RemoveAttributes;
use PHPUnit\Framework\TestCase;

/**
 * @package Phlib\XssSanitizer
 */
class RemoveAttributesTest extends TestCase
{
    /**
     * @dataProvider removeAttributesDataProvider
     */
    public function testRemoveAttributes(string $original, string $expected): void
    {
        $actual = (new RemoveAttributes())->filter($original);
        static::assertSame($expected, $actual);
    }

    public function removeAttributesDataProvider(): array
    {
        return [
            ['<body onload="alert(document.cookie);">', '<body >'],
            ['<img onerror="document.location=\'site\'">', '<img >'],
            ['<img onerror=`document.location=\'site\'`>', '<img >'],
            ['<img onerror=`document.location=\'site\'` >', '<img  >'],
            ['<img onerror=\'document.location="site"\'>', '<img >'],
            ['<img onerror=alert(\'XSS\') >', '<img  >'],
            ['<img onerror=alert(\'XSS\')>', '<img >'],
            ['<img onerror=alert(\'XSS\');>', '<img >'],

            ['<body fscommand="alert(document.cookie);">', '<body >'],
            ['<body seekSegmentTime="alert(document.cookie);">', '<body >'],

            ['<body onload="alert(document.cookie);" onerror="alert(document.cookie);">', '<body  >'],

            // https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#Non-alpha-non-digit_XSS
            ['<body onload!#$%&()*~+-_.,:;?@[/|\]^`="alert(document.cookie);">', '<body >'],

            // test falling back to pessimistic parser
            ['<body invalid="something\""onload="alert(\'XSS\')" >', '<body invalid="something\"" >'],
            ['<body invalid="something"/onload="alert(\'XSS\')" >', '<body invalid="something"/ >'],
            ['<a invalid="something""href="onload=alert(\'XSS\');" >', '<a invalid="something""href=" >'],

            // valid values
            [
                '<a href="http://my.website/index.php?onetimekey=abc">',
                '<a href="http://my.website/index.php?onetimekey=abc">',
            ],
            [
                '<a href="http://my.website/index.php?onload=dostuff">',
                '<a href="http://my.website/index.php?onload=dostuff">',
            ],
        ];
    }

    /**
     * @dataProvider dataRemoveAttributesExtra
     */
    public function testRemoveAttributesExtra(string $original, array $attributes, string $expected): void
    {
        $actual = (new RemoveAttributes($attributes))->filter($original);
        static::assertSame($expected, $actual);
    }

    public function dataRemoveAttributesExtra(): array
    {
        return [
            'extraNotRemovedWithDefaults' => [
                '<body data-phlib="alert(document.cookie);">',
                [],
                '<body data-phlib="alert(document.cookie);">',
            ],
            'extraRemoved' => [
                '<body data-phlib="alert(document.cookie);">',
                ['data-phlib'],
                '<body >',
            ],
            'defaultsStillRemovedWithExtras' => [
                '<body onload="alert(document.cookie);">',
                ['data-phlib'],
                '<body >',
            ],
        ];
    }
}
