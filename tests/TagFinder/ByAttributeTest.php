<?php

namespace Phlib\XssSanitizer\Test\TagFinder;

use Phlib\XssSanitizer\TagFinder;

/**
 * @package Phlib\XssSanitizer
 */
class ByAttributeTest extends \PHPUnit_Framework_TestCase
{
    public function testFindTagsCallbackArgs()
    {
        $tagFinder = new TagFinder\ByAttribute('title');

        $str = '<html><body><a title="something"></body></html>';
        $expectedFullTag    = '<a title="something">';
        $expectedAttributes = ' title="something"';
        $callback = function($fullTag, $attributes) use ($expectedFullTag, $expectedAttributes) {
            $this->assertEquals($expectedFullTag, $fullTag);
            $this->assertEquals($expectedAttributes, $attributes);
        };
        $tagFinder->findTags($str, $callback);
    }

    public function testFindTagsMultipleAttributes()
    {
        $tagFinder = new TagFinder\ByAttribute(['title', 'name']);

        $str = '<html><body><a title="something"><br /><a name="thename"></body></html>';
        $expectedFullTags = [
            '<a title="something">',
            '<a name="thename">',
        ];
        $actualFullTags = [];
        $callback = function($fullTag) use (&$actualFullTags) {
            $actualFullTags[] = $fullTag;
        };
        $tagFinder->findTags($str, $callback);

        $this->assertEquals(2, count($actualFullTags));
        $this->assertEquals($expectedFullTags, $actualFullTags);
    }

    /**
     * @dataProvider findTagsReplacementDataProvider
     * @param string $str
     * @param string $replacement
     * @param string $expected
     */
    public function testFindTagsReplacement($str, $replacement, $expected)
    {
        $tagFinder = new TagFinder\ByAttribute('title');

        $replacer = function() use ($replacement) {
            return $replacement;
        };
        $actual   = $tagFinder->findTags($str, $replacer);

        $this->assertEquals($expected, $actual);
    }

    public function findTagsReplacementDataProvider()
    {
        $r = '<!--replacement!-->';
        return [
            ['<html><body><a title="something"></body></html>', $r, "<html><body>$r</body></html>"],
            ['<html><body><a title="something"><a title="something"></body></html>', $r, "<html><body>$r$r</body></html>"],
        ];
    }

}
