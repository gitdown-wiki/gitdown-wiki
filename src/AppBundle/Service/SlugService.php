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
    
    public function desluggify($slug)
    {
        $text = str_replace($this->slugChar, ' ', $slug);
        
        $wordsToProcess = explode(' ', $text);
        $words = array();
        
        foreach($wordsToProcess as $word) {
            $strArray = str_split($word);
            $strArray[0] = strtoupper($strArray[0]);
            $word = implode($strArray);
            array_push($words, $word);
        }
        
        $text = implode(' ', $words);
        
        return $text;
    }
}
