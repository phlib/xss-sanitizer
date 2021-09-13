<?php

namespace Phlib\XssSanitizer\Test\Filter;

use Phlib\XssSanitizer\Filter\AttributeCleaner;
use Phlib\XssSanitizer\FilterInterface;

/**
 * @package Phlib/XssSanitiser
 */
class AttributeCleanerTest extends \PHPUnit_Framework_TestCase
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
                $str = str_ireplace('java script', 'javascript', $str);
                $str = str_ireplace('java&#115;cript', 'javascript', $str);
                return $str;
            }));
        $this->cleaner = $cleaner;
    }

    /**
     * @dataProvider cleanLinkHrefDataProvider
     * @param string $original
     * @param string $expected
     */
    public function testCleanLinkHref($original, $expected)
    {

        $actual = (new AttributeCleaner('href', $this->cleaner, ['a','link']))->filter($original);
        $this->assertEquals($expected, $actual);
    }

    public function cleanLinkHrefDataProvider()
    {
        return [
            ['<a href="javascript:alert(\'XSS\')">', '<a >'],
            ['<a href="javascript:alert(\'XSS\')" >', '<a  >'],
            ['<a href=\'javascript:alert("XSS")\'>', '<a >'],
            ['<a href=`javascript:alert(\'XSS\')`>', '<a >'],
            ['<a href=javascript:alert(\'XSS\')>', '<a >'],
            ['<a href=javascript:alert(\'XSS\') >', '<a  >'],
            ['<a href=javascript:alert(\'XSS\');>', '<a >'],

            // https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#Non-alpha-non-digit_XSS
            ['<a href!#$%&()*~+-_.,:;?@[/|\]^`=javascript:alert(\'XSS\');>', '<a >'],

            // Test that attribute content cleaner is being used
            ['<a href="java script:alert(\'XSS\')">', '<a >'],
            ['<a href=java&#115;cript:alert(\'XSS\')>', '<a >'],

            // Test some valid values for href
            ['<a href="http://google.com">', '<a href="http://google.com">'],

            ['<link href="javascript:alert(\'XSS\')">', '<link >'],
            ['<link href="javascript:alert(\'XSS\')" >', '<link  >'],
            ['<link href=\'javascript:alert("XSS")\'>', '<link >'],
            ['<link href=`javascript:alert(\'XSS\')`>', '<link >'],
            ['<link href=javascript:alert(\'XSS\')>', '<link >'],
            ['<link href=javascript:alert(\'XSS\') >', '<link  >'],
            ['<link href=javascript:alert(\'XSS\');>', '<link >'],
            ['<link href=\'http://google.com\'>', '<link href=\'http://google.com\'>'],
        ];
    }

    /**
     * @dataProvider cleanImgSrcDataProvider
     * @param string $original
     * @param string $expected
     */
    public function testCleanImgSrc($original, $expected)
    {
        $actual = (new AttributeCleaner('src', $this->cleaner, 'img'))->filter($original);
        $this->assertEquals($expected, $actual);
    }

    public function cleanImgSrcDataProvider()
    {
        return [
            ['<img src="javascript:alert(\'XSS\')">', '<img >'],
            ['<img src="javascript:alert(\'XSS\')" >', '<img  >'],
            ['<img src=\'javascript:alert("XSS")\'>', '<img >'],
            ['<img src=`javascript:alert(\'XSS\')`>', '<img >'],
            ['<img src=javascript:alert(\'XSS\')>', '<img >'],
            ['<img src=javascript:alert(\'XSS\') >', '<img  >'],
            ['<img src=javascript:alert(\'XSS\');>', '<img >'],
        ];
    }

    /**
     * @dataProvider cleanBackgroundAnyTagDataProvider
     * @param string $original
     * @param string $expected
     */
    public function testCleanBackgroundAnyTag($original, $expected)
    {
        $actual = (new AttributeCleaner('background', $this->cleaner))->filter($original);
        $this->assertEquals($expected, $actual);
    }

    public function cleanBackgroundAnyTagDataProvider()
    {
        return [
            ['<div background="javascript:alert(\'XSS\')">', '<div >'],
            ['<body background="javascript:alert(\'XSS\')">', '<body >'],
            ['<span background="javascript:alert(\'XSS\')">', '<span >'],
        ];
    }

}
