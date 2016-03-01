<?php

namespace Phlib\XssSanitizer\Filter;

use Phlib\XssSanitizer\Filter\AttributeContent;
use Phlib\XssSanitizer\FilterInterface;
use Phlib\XssSanitizer\FilterRunnerTrait;

/**
 * Class AttributeContentCleaner
 * @package Phlib\XssSanitizer\Filter
 */
class AttributeContentCleaner implements FilterInterface
{
    use FilterRunnerTrait;

    /**
     * @var FilterInterface[]
     */
    protected $filters;

    /**
     * AttributeContentCleaner constructor
     */
    public function __construct()
    {
        $this->initFilters();
    }

    /**
     * Filters the content of an attribute
     * This should be decoding UTF-8 and HTML entities, and compacting any exploded words which we're searching for
     *
     * e.g.
     *     \u006A a v a &#115; c r i p t:alert('XSS');
     * should become
     *     javascript:alert('XSS');
     *
     * @param string $str
     * @return string
     */
    public function filter($str)
    {
        return $this->runFilters($str, $this->filters);
    }

    /**
     * Create the filters and add to the filters array
     */
    protected function initFilters()
    {
        $this->filters = [
            new AttributeContent\DecodeUtf8(),
            new AttributeContent\DecodeEntities(),
            new AttributeContent\CompactExplodedWords(),
        ];
    }

}
