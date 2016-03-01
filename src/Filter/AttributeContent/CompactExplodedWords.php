<?php

namespace Phlib\XssSanitizer\Filter\AttributeContent;

use Phlib\XssSanitizer\FilterInterface;

/**
 * Class CompactExplodedWords
 * @package Phlib\XssSanitizer\Filter\AttributeContent
 */
class CompactExplodedWords implements FilterInterface
{

    /**
     * @var string
     */
    protected $wordsRegex;

    /**
     * CompactExplodedWords constructor
     */
    public function __construct()
    {
        $this->wordsRegex = $this->buildWordsRegex();
    }

    /**
     * Compacts certain potentially dangerous words which have had whtespace added between the letters
     *
     * e.g.
     *     j a v a s c r i p t
     * becomes
     *     javascript
     *
     * @param string $str
     * @return string
     */
    public function filter($str)
    {
        $str = preg_replace_callback(
            $this->wordsRegex,
            function ($matches) {
                return preg_replace('/\s+/', '', $matches[1]) . $matches[2];
            },
            $str
        );

        return $str;
    }

    /**
     * Build the regex for finding the exploded words
     *
     * @return string
     */
    protected function buildWordsRegex()
    {
        $rawWords = [
            'javascript',
            'refresh', /* @see Phlib\XssSanitizer\Filter\MetaRefresh */
        ];

        $words = [];
        foreach ($rawWords as $word) {
            $words[] = chunk_split($word, 1, '\s*');
        }

        return implode('', [
            '#(', implode('|', $words), ')(\W|$)#is',
        ]);
    }

}
