<?php

declare(strict_types=1);

namespace Phlib\XssSanitizer;

/**
 * @package Phlib\XssSanitizer
 */
interface FilterInterface
{
    public function filter(string $str): string;
}
