<?php

declare(strict_types=1);

namespace Phlib\XssSanitizer\Test;

use Phlib\XssSanitizer\Sanitizer;
use PHPUnit\Framework\TestCase;

/**
 * @package Phlib\XssSanitizer
 */
class SanitizerTest extends TestCase
{
    /**
     * @dataProvider sanitizeDataProvider
     */
    public function testSanitize(string $original, string $expected): void
    {
        $actual = (new Sanitizer())->sanitize($original);
        static::assertSame($expected, $actual);
    }

    public function sanitizeDataProvider(): array
    {
        return [
            ['<body><script>alert(\'XSS\');</script></body>', '<body></body>'],
            ['<body><iframe src="xss.com"></iframe></body>', '<body></body>'],
            ['<body><OBJECT TYPE="text/x-scriptlet" DATA="http://ha.ckers.org/scriptlet.html"></OBJECT></body>', '<body></body>'],

            ['<body><scr<script></script>ipt>alert(\'XSS\');</script></body>', '<body></body>'],

            // script tags should be escaped if we can't remove the whole block
            ['<body><script>alert(\'XSS\');</body>', '<body>&lt;script>alert(\'XSS\');</body>'],

            ['<a href="javascript:alert(\'XSS\')">', '<a >'],
            ['<a href="javascript:alert(\'XSS\')" >', '<a  >'],
            ['<a href=\'javascript:alert("XSS")\'>', '<a >'],
            ['<a href=`javascript:alert(\'XSS\')`>', '<a >'],
            ['<a href=javascript:alert(\'XSS\')>', '<a >'],
            ['<a href=javascript:alert(\'XSS\') >', '<a  >'],
            ['<a href=javascript:alert(\'XSS\');>', '<a >'],
            ['<a href=" j a v a s c r i p t :alert(\'XSS\')" >', '<a  >'],

            ['<body onload="alert(document.cookie);">', '<body >'],
            ['<img onerror="document.location=\'site\'">', '<img >'],
            ['<img onerror=`document.location=\'site\'`>', '<img >'],
            ['<img onerror=`document.location=\'site\'` >', '<img  >'],
            ['<img onerror=\'document.location="site"\'>', '<img >'],
            ["<img onerror='a\tl\te rt(\"XSS\")'>", '<img >'],
            ['<img onerror=alert(\'XSS\') >', '<img  >'],
            ['<img onerror=alert(\'XSS\')>', '<img >'],
            ['<img onerror=alert(\'XSS\');>', '<img >'],

            ['<INPUT TYPE="IMAGE" SRC="javascript:alert(\'XSS\');">', '<INPUT TYPE="IMAGE" >'],
            ['<body background="javascript:alert(\'XSS\');">', '<body >'],
            ['<link href="javascript:alert(\'XSS\');">', '<link >'],
            ['<bgsound src="javascript:alert(\'XSS\');">', '<bgsound >'],
            ['<form action="javascript:alert(\'XSS\');">', '<form >'],
            ['<svg/onload=alert(\'XSS\')>', '<svg/>'],
        ];
    }

    public function testSanitizeArray(): void
    {
        $strings = [
            '<body><script>alert(\'XSS\');</script></body>',
            '<img onerror=alert(\'XSS\')>',
        ];

        $expected = [
            '<body></body>',
            '<img >',
        ];

        $actual = (new Sanitizer())->sanitizeArray($strings);

        static::assertSame($expected, $actual);
    }
}
