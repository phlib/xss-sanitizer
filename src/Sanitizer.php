<?php

namespace Phlib\XssSanitizer;

use Phlib\XssSanitizer\Filter;

/**
 * Class Sanitizer
 * @package Phlib\XssSanitizer
 */
class Sanitizer
{
    use FilterRunnerTrait;

    /**
     * @var FilterInterface[]
     */
    protected $filters;

    /**
     * Sanitizer constructor
     */
    public function __construct()
    {
        $this->initFilters();
    }

    /**
     * Sanitize a HTML string
     *
     * @param string $str
     * @return string
     */
    public function sanitize($str)
    {
        $str = $this->runFilters($str, $this->filters);

        return $str;
    }

    /**
     * Create the filters and add to the filters array
     */
    protected function initFilters()
    {
        $this->filters = [];

        $attributeContentCleaner = new Filter\AttributeContentCleaner();
        $this->filters[] = new Filter\AttributeCleaner('href', $attributeContentCleaner, ['a','link']);
        $this->filters[] = new Filter\AttributeCleaner('src', $attributeContentCleaner, ['img','input', 'bgsound']);
        $this->filters[] = new Filter\AttributeCleaner('action', $attributeContentCleaner, ['form']);
        $this->filters[] = new Filter\AttributeCleaner('background', $attributeContentCleaner);
        $this->filters[] = new Filter\EscapeTags(['script', 'iframe']);
        $this->filters[] = new Filter\RemoveAttributes();
        $this->filters[] = new Filter\MetaRefresh($attributeContentCleaner);
    }
}
