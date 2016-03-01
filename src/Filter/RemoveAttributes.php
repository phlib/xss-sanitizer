<?php

namespace Phlib\XssSanitizer\Filter;

use Phlib\XssSanitizer\AttributeFinder;
use Phlib\XssSanitizer\FilterInterface;
use Phlib\XssSanitizer\TagFinder;

/**
 * Class RemoveAttributes
 * @package Phlib\XssSanitizer\Filter
 */
class RemoveAttributes implements FilterInterface
{
    /**
     * @var array
     */
    protected $attributes;

    /**
     * @var TagFinder
     */
    protected $tagFinder;

    /**
     * @var AttributeFinder
     */
    protected $attributeFinder;

    /**
     * RemoveAttributes constructor
     */
    public function __construct()
    {
        $this->attributes = [
            'on\w+',
            'fscommand',
            'seeksegmenttime',
        ];

        $this->tagFinder       = new TagFinder($this->attributes, TagFinder::BY_ATTR);
        $this->attributeFinder = new AttributeFinder($this->attributes);
    }

    /**
     * Filter unwanted attributes from tags
     *
     * This includes event handler attributes ('onload', 'onclick' etc.)
     * e.g. '<body onload="alert('XSS');">'
     *
     * @param string $str
     * @return string
     */
    public function filter($str)
    {
        $str = $this->tagFinder->findTags($str, function($fullTag, $attributes) {
            return $this->removeAttribute($fullTag, $attributes);
        });

        return $str;
    }

    /**
     * Removes unwanted attributes from a particular tag
     *
     * @param string $fullTag (e.g. '<a onclick="alert('XSS');">')
     * @param string $attributes (e.g. 'a onclick="alert('XSS');"')
     * @return string
     */
    protected function removeAttribute($fullTag, $attributes)
    {
        $replacement = $this->attributeFinder->findAttributes($attributes, function() {
            return '';
        });

        return str_ireplace($attributes, $replacement, $fullTag);
    }

}
