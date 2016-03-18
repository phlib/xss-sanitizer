<?php

namespace Phlib\XssSanitizer\TagFinder;

use Phlib\XssSanitizer\TagFinderInterface;

/**
 * Class ByTag
 * @package Phlib\XssSanitizer
 */
class ByTag implements TagFinderInterface
{
    /**
     * @var string
     */
    protected $searchRegex;

    /**
     * ByTag constructor
     * @param string|string[] $tags
     */
    public function __construct($tags)
    {
        $this->searchRegex = $this->initSearchRegex($tags);
    }

    /**
     * Given a full html string, finds the required tags by either tag name and calls the callback,
     * providing the full tag string and the attributes string
     *
     * The return value is used to replace the full tag string
     *
     * e.g. for an tag finder which is looking for an img tag
     * for the string
     *     '<body><img src="something"></body'
     * the callback will provide
     *     $fullTag:    '<img src="something">'
     *     $attributes: ' src="something"'
     * and the return from the callback would replace the $fullTag in the original string
     *
     * @param string $str
     * @param callable $callback
     * @return string
     */
    public function findTags($str, callable $callback)
    {
        return preg_replace_callback(
            $this->searchRegex,
            function($matches) use ($callback) {
                return $callback($matches[0], $matches[1]);
            },
            $str
        );
    }

    /**
     * Build the search regex based on the tags specified
     *
     * @param string|string[] $tags
     * @return string
     */
    protected function initSearchRegex($tags)
    {
        if (is_array($tags)) {
            $tags = '(?:' . implode('|', $tags) . ')';
        }
        return implode('', [
            '#<',
            $tags,
            '[^a-z0-9>]+([^>]*)(?:>|$)',
            '#si'
        ]);
    }
}
