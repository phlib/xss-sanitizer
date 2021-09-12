<?php

declare(strict_types=1);

namespace Phlib\XssSanitizer\Test\Filter;

use Phlib\XssSanitizer\Filter\AttributeContentCleaner;
use PHPUnit\Framework\TestCase;

/**
 * @package Phlib\XssSanitizer
 */
class AttributeContentCleanerTest extends TestCase
{
    /**
     * @dataProvider cleanAttributeContentDataProvider
     */
    public function testCleanAttributeContent(string $original, string $expected): void
    {
        $actual = (new AttributeContentCleaner())->filter($original);
        static::assertEquals($expected, $actual);
    }

    public function cleanAttributeContentDataProvider(): array
    {
        return [
            ['java&#115;cript:alert(document.cookie)', 'javascript:alert(document.cookie)'],
            ["ja\tva&#115;cript:alert(document.cookie)", 'javascript:alert(document.cookie)'],
            ['java &#115;cript:alert(document.cookie)', 'javascript:alert(document.cookie)'],
            ['java&#38;#115;cript:alert(document.cookie)', 'javascript:alert(document.cookie)'],
            ['java\u0026#115;cript:alert(document.cookie)', 'javascript:alert(document.cookie)'],
            ['java\u0073cript:alert(document.cookie)', 'javascript:alert(document.cookie)'],
            ['java\&#117;0073cript:alert(document.cookie)', 'javascript:alert(document.cookie)'],
            ['j&#09;a&#09;v&#09;a&#09;s&#09;c&#09;r&#09;i&#09;p&#09;t:alert(document.cookie)', 'javascript:alert(document.cookie)'],

            // https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#IMG_onerror_and_javascript_alert_encode
            ['&#0000106&#0000097&#0000118&#0000097&#0000115&#0000099&#0000114&#0000105&#0000112&#0000116&#0000058&#00' .
                '00097&#0000108&#0000101&#0000114&#0000116&#0000040&#0000039&#0000088&#0000083&#0000083&#0000039&#000' .
                '0041', "javascript:alert('XSS')"],
            // https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#Decimal_HTML_character_references
            ['&#106;&#97;&#118;&#97;&#115;&#99;&#114;&#105;&#112;&#116;&#58;&#97;&#108;&#101;&#114;&#116;&#40;&#39;&#' .
                '88;&#83;&#83;&#39;&#41;', "javascript:alert('XSS')"],
            // https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#Decimal_HTML_character_references_without_trailing_semicolons
            ['&#0000106&#0000097&#0000118&#0000097&#0000115&#0000099&#0000114&#0000105&#0000112&#0000116&#0000058&#00' .
                '00097&#0000108&#0000101&#0000114&#0000116&#0000040&#0000039&#0000088&#0000083&#0000083&#0000039&#000' .
                '0041', "javascript:alert('XSS')"],
            // https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#Hexadecimal_HTML_character_references_without_trailing_semicolons
            ['&#x6A&#x61&#x76&#x61&#x73&#x63&#x72&#x69&#x70&#x74&#x3A&#x61&#x6C&#x65&#x72&#x74&#x28&#x27&#x58&#x53&#x' .
                '53&#x27&#x29', "javascript:alert('XSS')"],
            // https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#Embedded_tab
            ['jav	ascript:alert(\'XSS\');', "javascript:alert('XSS');"],
            // https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#Embedded_Encoded_tab
            ['jav&#x09;ascript:alert(\'XSS\');', "javascript:alert('XSS');"],
            // https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#Embedded_newline_to_break_up_XSS
            ['jav&#x0A;ascript:alert(\'XSS\');', "javascript:alert('XSS');"],
            // https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#Embedded_carriage_return_to_break_up_XSS
            ['jav&#x0D;ascript:alert(\'XSS\');', "javascript:alert('XSS');"],
        ];
    }
}
