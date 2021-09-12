<?php

declare(strict_types=1);

namespace Phlib\XssSanitizer\Test\Filter;

use Phlib\XssSanitizer\Filter\MetaRefresh;
use Phlib\XssSanitizer\FilterInterface;
use PHPUnit\Framework\TestCase;

/**
 * @package Phlib\XssSanitizer
 */
class MetaRefreshTest extends TestCase
{
    /**
     * @var FilterInterface
     */
    protected $cleaner;

    public function setUp(): void
    {
        $cleaner = $this->createMock(FilterInterface::class);
        $cleaner->method('filter')
            ->willReturnCallback(function ($str): string {
                $str = str_ireplace('re fresh', 'refresh', $str);
                $str = str_ireplace('re&#115;fresh', 'refresh', $str);
                return $str;
            });
        $this->cleaner = $cleaner;
    }

    /**
     * @dataProvider removeMetaDataProvider
     */
    public function testRemoveMeta(string $original, string $expected): void
    {
        $actual = (new MetaRefresh($this->cleaner))->filter($original);
        static::assertEquals($expected, $actual);
    }

    public function removeMetaDataProvider(): array
    {
        return [
            // https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#META
            ['<META HTTP-EQUIV="refresh" CONTENT="0;url=javascript:alert(\'XSS\');">', ''],
            // https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#META_using_data
            ['<META HTTP-EQUIV="refresh" CONTENT="0;url=data:text/html base64,PHNjcmlwdD5hbGVydCgnWFNTJyk8L3NjcmlwdD4K">', ''],
            // https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#META_with_additional_URL_parameter
            ['<META HTTP-EQUIV="refresh" CONTENT="0; URL=http://;URL=javascript:alert(\'XSS\');">', ''],

            // https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#Non-alpha-non-digit_XSS
            ['<META HTTP-EQUIV!#$%&()*~+-_.,:;?@[/|\]^`="refresh" CONTENT="0;url=javascript:alert(\'XSS\');">', ''],

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
