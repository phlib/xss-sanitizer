<?php

namespace Phlib\XssSanitizer\Test\Filter;

use Phlib\XssSanitizer\Filter\RemoveBlocks;

/**
 * @package Phlib\XssSanitizer
 */
class RemoveBlocksTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider removeBlocksDataProvider
     * @param string $original
     * @param string $expected
     */
    public function testRemoveBlocks($original, $expected)
    {

        $actual = (new RemoveBlocks('script'))->filter($original);
        $this->assertEquals($expected, $actual);
    }

    public function removeBlocksDataProvider()
    {
        return [
            ['<body><script>alert(\'XSS\');</script></body>', '<body></body>'],
            ["<body><script>alert('XSS');\nalert('Multi line');</script></body>", '<body></body>'],

            /**
             *  this shows why this filter should be used in conjunction with @see \Phlib\XssSanitizer\Filter\EscapeTags
             */
            ['<body><scri<script>pt>alert(\'XSS\');<scri</script>pt></body>', '<body><script></body>'],
            ['<body><script>var x = "</script>";alert(\'XSS\');</script></body>', '<body>";alert(\'XSS\');</script></body>'],
        ];
    }

}
