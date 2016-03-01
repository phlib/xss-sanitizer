<?php

namespace Phlib\XssSanitizer;

/**
 * Trait FilterRunnerTrait
 * @package Phlib\XssSanitizer
 */
trait FilterRunnerTrait
{

    /**
     * Run the filters repeatedly until they no longer change the string
     *
     * @param string $str
     * @param FilterInterface[] $filters
     * @return string
     */
    protected function runFilters($str, $filters)
    {
        do {
            $pre = $str;
            $str = $this->applyEachFilter($str, $filters);
        } while ($pre !== $str);

        return $str;
    }

    /**
     * Apply each filter in the filters array
     *
     * @param string $str
     * @param FilterInterface[] $filters
     * @return string
     */
    protected function applyEachFilter($str, $filters)
    {
        foreach ($filters as $filter) {
            $str = $filter->filter($str);
        }
        return $str;
    }

}
