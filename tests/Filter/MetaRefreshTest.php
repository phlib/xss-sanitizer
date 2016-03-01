<?php

namespace Phlib\XssSanitizer\Test\Filter;

use Phlib\XssSanitizer\Filter\MetaRefresh;
use Phlib\XssSanitizer\FilterInterface;

/**
 * Class MetaRefreshTest
 * @package Phlib\XssSanitizer\Test\Filter
 */
class MetaRefreshTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FilterInterface
     */
    protected $cleaner;

    public function setUp()
    {
        $cleaner = $this->getMock(FilterInterface::class);
        $cleaner->expects($this->any())
            ->method('filter')
            ->will($this->returnCallback(function($str) {
                $str = str_ireplace('re fresh', 'refresh', $str);
                $str = str_ireplace('re&#115;fresh', 'refresh', $str);
                return $str;
            }));
        $this->cleaner = $cleaner;
    }

    /**
     * @dataProvider removeMetaDataProvider
     * @param string $original
     * @param string $expected
     */
    public function testRemoveMeta($original, $expected)
    {
        $actual = (new MetaRefresh($this->cleaner))->filter($original);
        $this->assertEquals($expected, $actual);
    }

    public function removeMetaDataProvider()
    {
        return [
            // https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#META
            ['<META HTTP-EQUIV="refresh" CONTENT="0;url=javascript:alert(\'XSS\');">', ''],
            // https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#META_using_data
            ['<META HTTP-EQUIV="refresh" CONTENT="0;url=data:text/html base64,PHNjcmlwdD5hbGVydCgnWFNTJyk8L3NjcmlwdD4K">', ''],
            // https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#META_with_additional_URL_parameter
            ['<META HTTP-EQUIV="refresh" CONTENT="0; URL=http://;URL=javascript:alert(\'XSS\');">', ''],

            ['<META HTTP-EQUIV=refresh CONTENT="0; URL=http://;URL=javascript:alert(\'XSS\');">', ''],
            ['<META HTTP-EQUIV=`refresh` CONTENT="0; URL=http://;URL=javascript:alert(\'XSS\');">', ''],
            ['<META HTTP-EQUIV=\'refresh\' CONTENT="0; URL=http://;URL=javascript:alert(\'XSS\');">', ''],

            // Test attribute cleaner is used
            ['<META HTTP-EQUIV="re fresh" CONTENT="0; URL=http://;URL=javascript:alert(\'XSS\');">', ''],
            ['<META HTTP-EQUIV="re&#115;fresh" CONTENT="0; URL=http://;URL=javascript:alert(\'XSS\');">', ''],

            // Test valid meta tag
            ['<meta http-equiv= x-ua-compatible content= ie=edge>', '<meta http-equiv= x-ua-compatible content= ie=edge>'],
            ['<meta http-equiv="x-ua-compatible" content="ie=edge">', '<meta http-equiv="x-ua-compatible" content="ie=edge">'],
        ];
    }

}
