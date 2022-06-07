<?php

declare(strict_types=1);

namespace Phlib\XssSanitizer\Test\Filter;

use Phlib\XssSanitizer\Filter\AttributeCleaner;
use Phlib\XssSanitizer\FilterInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @package Phlib/XssSanitiser
 */
class AttributeCleanerTest extends TestCase
{
    /**
     * @var FilterInterface|MockObject
     */
    private MockObject $cleaner;

    protected function setUp(): void
    {
        $cleaner = $this->createMock(FilterInterface::class);
        $cleaner->method('filter')
            ->willReturnCallback(function ($str): string {
                $str = str_ireplace('java script', 'javascript', $str);
                $str = str_ireplace('java&#115;cript', 'javascript', $str);
                return $str;
            });
        $this->cleaner = $cleaner;
    }

    /**
     * @dataProvider cleanLinkHrefDataProvider
     */
    public function testCleanLinkHref(string $original, string $expected): void
    {
        $actual = (new AttributeCleaner('href', $this->cleaner, ['a', 'link']))->filter($original);
        static::assertSame($expected, $actual);
    }

    public function cleanLinkHrefDataProvider(): array
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
     */
    public function testCleanImgSrc(string $original, string $expected): void
    {
        $actual = (new AttributeCleaner('src', $this->cleaner, 'img'))->filter($original);
        static::assertSame($expected, $actual);
    }

    public function cleanImgSrcDataProvider(): array
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
     */
    public function testCleanBackgroundAnyTag(string $original, string $expected): void
    {
        $actual = (new AttributeCleaner('background', $this->cleaner))->filter($original);
        static::assertSame($expected, $actual);
    }

    public function cleanBackgroundAnyTagDataProvider(): array
    {
        return [
            'remove-attr-div-dblquote' => [
                '<div background="javascript:alert(\'XSS\')">',
                '<div >',
            ],
            'remove-attr-div-snglquote' => [
                '<div background=\'javascript:alert("XSS")\'>',
                '<div >',
            ],
            'remove-attr-div-noquote' => [
                '<div background=javascript:alert(\'XSS\')>',
                '<div >',
            ],
            'remove-attr-body-dblquote' => [
                '<body background="javascript:alert(\'XSS\')">',
                '<body >',
            ],
            'remove-attr-body-snglquote' => [
                '<body background=\'javascript:alert("XSS")\'>',
                '<body >',
            ],
            'remove-attr-body-noquote' => [
                '<body background=javascript:alert(\'XSS\')>',
                '<body >',
            ],
            'remove-attr-span-dblquote' => [
                '<span background="javascript:alert(\'XSS\')">',
                '<span >',
            ],
            'remove-attr-span-snglquote' => [
                '<span background=\'javascript:alert("XSS")\'>',
                '<span >',
            ],
            'remove-attr-span-noquote' => [
                '<span background=javascript:alert(\'XSS\')>',
                '<span >',
            ],

            'valid-pessimistic-empty-dblquote' => [
                '<div style="color:red;" background="">',
                '<div style="color:red;" background="">',
            ],
            'valid-pessimistic-empty-snglquote' => [
                '<div style="color:red;" background=\'\'>',
                '<div style="color:red;" background=\'\'>',
            ],
            'valid-pessimistic-empty-noquote' => [
                '<div style="color:red;" background= >',
                '<div style="color:red;" background= >',
            ],
        ];
    }
}
