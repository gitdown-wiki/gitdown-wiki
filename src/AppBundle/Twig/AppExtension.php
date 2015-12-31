<?php

namespace AppBundle\Twig;

use AppBundle\Service\MarkdownService;

class AppExtension extends \Twig_Extension
{
    
    protected $parser;
    
    function __construct(MarkdownService $parser)
    {
        $this->parser = $parser;
    }
    
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter(
                'markdown',
                array($this, 'markdownToHtml'),
                array('is_safe' => array('html'))
            ),
        );
    }
    
    public function markdownToHtml($content)
    {
        return $this->parser->toHtml($content);
    }
    
    public function getName()
    {
        return 'app_extension';
    }
}
