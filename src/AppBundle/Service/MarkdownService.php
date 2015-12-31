<?php

namespace AppBundle\Service;

use \Parsedown;

/**
 *
 */
class MarkdownService
{
    
    protected $parser;
    
    function __construct()
    {
        $this->parser = new Parsedown();
    }
    
    public function toHtml($text)
    {
        $html = $this->parser->text($text);
        
        return $html;
    }
}
