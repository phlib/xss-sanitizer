<?php

namespace Phlib\XssSanitizer\Test\Filter\AttributeContents;

use Phlib\XssSanitizer\Filter\AttributeContent\DecodeEntities;

/**
 * Class DecodeEntitiesTest
 * @package Phlib\XssSanitizer\Test\Filter\AttributeContents
 */
class DecodeEntitiesTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider decodeDataProvider
     * @param string $original
     * @param string $expected
     */
    public function testDecode($original, $expected)
    {
        $actual = (new DecodeEntities())->filter($original);
        $this->assertEquals($expected, $actual);
    }

    public function decodeDataProvider()
    {
        return [
            ['xx &#65; xx', 'xx A xx'],
            ['xx &#0065; xx', 'xx A xx'],
            ['xx &#0000000000000000065; xx', 'xx A xx'],
            ['xx &#65 xx', 'xx A xx'],
            ['xx &#00065 xx', 'xx A xx'],
            ['xx &#00000000000000065 xx', 'xx A xx'],

            ['xx &#x6a; xx', 'xx j xx'],
            ['xx &#x006a; xx', 'xx j xx'],
            ['xx &#x000000000000000006a; xx', 'xx j xx'],
            ['xx &#x6a xx', 'xx j xx'],
            ['xx &#x0006a xx', 'xx j xx'],
            ['xx &#x0000000000000006a xx', 'xx j xx'],
        ];
    }

}
