<?php

namespace Phlib\XssSanitizer\Filter;

use Phlib\XssSanitizer\FilterInterface;

/**
 * Class RemoveBlocks
 * @package Phlib\XssSanitizer\Filter
 */
class RemoveBlocks implements FilterInterface
{
    /**
     * @var string
     */
    protected $searchRegex;

    /**
     * RemoveBlocks constructor
     * @param string|string[] $tags
     */
    public function __construct($tags)
    {
        $this->searchRegex = $this->initSearchRegex($tags);
    }

    /**
     * Filter tags out of the HTML by removing whole blocks (from opening tag to closing tag)
     *
     * This filter should be used in conjunction with @see \Phlib\XssSanitizer\Filter\EscapeTags to ensure that any
     * tags which are not picked up will be escaped
     *
     * e.g.
     *     <body><script type="text/javascript">alert('XSS');</script></body>
     * becomes
     *     <body></body>
     *
     * @param string $str
     * @return string
     */
    public function filter($str)
    {
        $str = preg_replace($this->searchRegex, '', $str);

        return $str;
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
            '#',
                // open tag
                '<',
                '(', $tags, ')',
                '([^>]*?)',
                '>',
                // content
                '.*?',
                // closing tag
                '</',
                '\1',
                '([^>]*?)',
                '(>|$)',
            '#si',
        ]);
    }
}
