<?php

namespace Phlib\XssSanitizer;

/**
 * Interface FilterInterface
 * @package Phlib\XssSanitizer
 */
interface FilterInterface
{

    /**
     * Apply this filter to the string, returning the filtered string
     *
     * @param string $str
     * @return string
     */
    public function filter($str);

}
