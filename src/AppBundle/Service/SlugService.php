<?php

namespace AppBundle\Service;

class SlugService
{
    protected $slugChar;
    
    function __construct($slugChar = '-')
    {
        $this->slugChar = $slugChar;
    }
    
    public function slugify($text)
    {
        // replace non letter or digits by -
        $text = preg_replace('~[^\\pL\d]+~u', $this->slugChar, $text);

        // trim
        $text = trim($text, $this->slugChar);

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // lowercase
        $text = strtolower($text);

        // remove unwanted characters
        $text = preg_replace('~[^' . $this->slugChar . '\w]+~', '', $text);

        if (empty($text))
        {
            throw new \InvalidArgumentException('You can not slugify an empty string');
        }
        return $text;
    }
}
