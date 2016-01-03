<?php

namespace AppBundle\Twig;

use AppBundle\Service\MarkdownService;
use Gitonomy\Git\Tree;

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
            new \Twig_SimpleFilter(
                'isTree',
                array($this, 'checkIfTree')
            ),
            new \Twig_SimpleFilter(
                'pageFromMarkdown',
                array($this, 'generatePageFromMarkdown')
            ),
            new \Twig_SimpleFilter(
                'extendPagePath',
                array($this, 'extendPagePath')
            )
        );
    }
    
    public function markdownToHtml($content)
    {
        return $this->parser->toHtml($content);
    }
    
    public function checkIfTree($object)
    {
        return $object instanceof Tree;
    }
    
    public function generatePageFromMarkdown($markdown, $basePath)
    {
        $path = (strlen($basePath) > 0) ? $basePath . '/' : '';
        return  $path . str_replace('.md', '', $markdown);
    }
    
    public function extendPagePath($basePath, $extension)
    {
        $path = $basePath;
        $path .= (strlen($basePath) > 0) ? '/' : '';
        return $path . $extension;
    }
    
    public function getName()
    {
        return 'app_extension';
    }
}
