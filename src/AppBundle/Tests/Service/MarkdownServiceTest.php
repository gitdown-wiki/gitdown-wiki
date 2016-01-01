<?php

namespace AppBundle\Tests\Service;

use \AppBundle\Service\MarkdownService;

class MarkdownServiceTest extends \PHPUnit_Framework_TestCase
{
    public function testParser()
    {
        $extension = new MarkdownService();
        
        $markup = $extension->toHtml("# Test\n\nThis is just a test");
        
        $this->assertEquals("<h1>Test</h1>\n<p>This is just a test</p>", $markup);
    }
}
