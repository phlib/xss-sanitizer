<?php

namespace Phlib\XssSanitizer\Test;

use Phlib\XssSanitizer\Sanitizer;

/**
 * Class SanitizerTest
 * @package Phlib\XssSanitizer\Test
 */
class SanitizerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider sanitizeDataProvider
     * @param string $original
     * @param string $expected
     */
    public function testSanitize($original, $expected)
    {
        $actual = (new Sanitizer())->sanitize($original);
        $this->assertEquals($expected, $actual);
    }

    public function sanitizeDataProvider()
    {
        return [
            ['<body><script>alert(\'XSS\');</script></body>', '<body>&lt;script>alert(\'XSS\');&lt;/script></body>'],
            ['<body><scri<script>pt>alert(\'XSS\');<scri</script>pt></body>', '<body><scri&lt;script>pt>alert(\'XSS\');<scri&lt;/script>pt></body>'],

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
        ];
    }

}
