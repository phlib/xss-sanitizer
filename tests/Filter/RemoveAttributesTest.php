<?php

namespace Phlib\XssSanitizer\Test\Filter;

use Phlib\XssSanitizer\Filter\RemoveAttributes;

/**
 * Class RemoveAttributesTest
 * @package Phlib\XssSanitizer\Test\Filter
 */
class RemoveAttributesTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider removeAttributesDataProvider
     * @param string $original
     * @param string $expected
     */
    public function testRemoveAttributes($original, $expected)
    {

        $actual = (new RemoveAttributes())->filter($original);
        $this->assertEquals($expected, $actual);
    }

    public function removeAttributesDataProvider()
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

            // valid values
            ['<a href="http://my.website/index.php?onetimekey=abc">', '<a href="http://my.website/index.php?onetimekey=abc">'],
        ];
    }

}
