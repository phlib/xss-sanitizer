<?php

namespace Phlib\XssSanitizer\Filter;

use Phlib\XssSanitizer\FilterInterface;

/**
 * Class EscapeTags
 *
 * @package Phlib\XssSanitizer\Filter
 */
class EscapeTags implements FilterInterface
{

    /**
     * @var string
     */
    protected $searchRegex;

    /**
     * EscapeTags constructor
     * @param string|string[] $tags
     */
    public function __construct($tags)
    {
        $this->searchRegex = $this->initSearchRegex($tags);
    }

    /**
     * Filter tags by html encoding the opening angle bracket
     *
     * e.g.
     *     <script type="text/javascript">alert('XSS');</script>
     * becomes
     *     &lt;script type="text/javascript">alert('XSS');&lt;/script>
     *
     * @param string $str
     * @return string
     */
    public function filter($str)
    {
        $str = preg_replace($this->searchRegex, '&lt;\1', $str);

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
                '<',
                '(/?', $tags , ')',
            '#si',
        ]);
    }

}
