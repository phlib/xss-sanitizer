<?php

namespace Phlib\XssSanitizer\Filter;

use Phlib\XssSanitizer\FilterInterface;
use Phlib\XssSanitizer\FilterRunnerTrait;

/**
 * Class FilterRunner
 * @package Phlib\XssSanitizer\Filter
 */
class FilterRunner implements FilterInterface
{
    use FilterRunnerTrait;

    /**
     * @var FilterInterface[]
     */
    protected $filters;

    /**
     * FilterRunner constructor
     * @param FilterInterface|FilterInterface[] $filters
     */
    public function __construct($filters)
    {
        if (!is_array($filters)) {
            $filters = [$filters];
        }
        $this->filters = $filters;
    }

    /**
     * Runs each of the filters against the string repeatedly
     *
     * @param string $str
     * @return string
     */
    public function filter($str)
    {
        return $this->runFilters($str, $this->filters);
    }

}
