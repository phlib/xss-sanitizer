<?php

namespace Phlib\XssSanitizer;

/**
 * Class TagFinder
 * @package Phlib\XssSanitizer
 */
class TagFinder
{
    const BY_TAG  = 1;
    const BY_ATTR = 2;

    /**
     * @var string
     */
    protected $searchRegex;

    /**
     * TagFinder constructor
     * @param string|string[] $searchValues
     * @param int $mode                         Should be either TagFinder::BY_TAG or TagFinder::BY_ATTR
     */
    public function __construct($searchValues, $mode = self::BY_TAG)
    {
        if ($mode == self::BY_ATTR) {
            $this->searchRegex = $this->initByAttrRegex($searchValues);
        } else {
            $this->searchRegex = $this->initByTagRegex($searchValues);
        }
    }

    /**
     * Given a full html string, finds the required tags by either tag name or attribute and calls the callback,
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
    protected function initByTagRegex($tags)
    {
        if (is_array($tags)) {
            $tags = '(?:' . implode('|', $tags) . ')';
        }
        return implode('', [
            '#<',
            $tags,
            '[^a-z0-9>]+([^>]*?)(?:>|$)',
            '#i'
        ]);
    }

    /**
     * Build the search regex based on the attributes specified
     *
     * @param string|string[] $attributes
     * @return string
     */
    protected function initByAttrRegex($attributes)
    {
        if (is_array($attributes)) {
            $attributes = '(?:' . implode('|', $attributes) . ')';
        }
        return implode('',[
            '#',
                '<[a-z]+([^>]+',
                '(?<!\w)',
                    $attributes,
                '[^0-9a-z"\'=]*', // https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#Non-alpha-non-digit_XSS
                '=[^>]+)>',
            '#si',
        ]);
    }
}
