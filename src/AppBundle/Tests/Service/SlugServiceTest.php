<?php

namespace AppBundle\Tests\Service;

use AppBundle\Service\SlugService;

class SlugServiceTest extends \PHPUnit_Framework_TestCase
{
    public function testSlugify()
    {
        $slugService = new SlugService();
        
        $slug = $slugService->slugify('This is just a test.~');
        
        $this->assertEquals('this-is-just-a-test', $slug);
    }
    
    public function testCustomChar()
    {
        $slugService = new SlugService('_');
        
        $slug = $slugService->slugify('This is just a-test.~');
        
        $this->assertEquals('this_is_just_a_test', $slug);
    }
    
    public function testEmpty()
    {
        $slugService = new SlugService();
        
        $this->setExpectedException('\InvalidArgumentException');
        
        $slug = $slugService->slugify(' ');
    }
    
    public function testDesluggify()
    {
        $slugService = new SlugService();
        $sentence = $slugService->desluggify('this-is-just-a-test');
        
        $this->assertEquals('This Is Just A Test', $sentence);
    }
}