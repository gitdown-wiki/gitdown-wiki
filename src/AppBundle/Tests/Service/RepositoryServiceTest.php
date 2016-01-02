<?php

namespace AppBundle\Tests\Service;

use \AppBundle\Service\RepositoryService;
use \Gitonomy\Git\Repository;
use Symfony\Component\Filesystem\Filesystem;

class RepositoryServiceTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        $repoName = 'test';
        $rootPath = 'app/data';
        
        $fs = new Filesystem();
        
        $fs->remove(realpath(__DIR__ . '/../../../../' . $rootPath . '/' . $repoName));
    }
    
    public function testRepositoryGetter()
    {
        $repoName = 'test';
        $rootPath = 'app/data';
        
        $repoPath = __DIR__ . '/../../../../' . $rootPath . '/' . $repoName;
        
        $fs = new Filesystem();
        $fs->mkdir($repoPath);
        
        $repo = \Gitonomy\Git\Admin::init($repoPath, false);
        
        $repoService = new RepositoryService($rootPath);
        $testRepo = $repoService->getRepository($repoName);
        
        $this->assertInstanceOf('\Gitonomy\Git\Repository', $testRepo);
    }
    
    public function testRepositoryWithoutFolder()
    {
        $repoName = 'test';
        $rootPath = 'app/data';
        
        $repoPath = __DIR__ . '/../../../../' . $rootPath . '/' . $repoName;
        
        $this->setExpectedException('\InvalidArgumentException');
        
        $repoService = new RepositoryService($rootPath);
        $testRepo = $repoService->getRepository($repoName);
    }
    
    public function testWithoutValidRepo()
    {
        $repoName = 'test';
        $rootPath = 'app/data';
        
        $repoPath = __DIR__ . '/../../../../' . $rootPath . '/' . $repoName;
        
        $fs = new Filesystem();
        $fs->mkdir($repoPath);
        
        $this->setExpectedException('\InvalidArgumentException');
        
        $repoService = new RepositoryService($rootPath);
        $testRepo = $repoService->getRepository($repoName);
    }
    
    public function testRepositoryCreation()
    {
        $repoName = 'test';
        $rootPath = 'app/data';
        
        $repoPath = __DIR__ . '/../../../../' . $rootPath . '/' . $repoName;
        
        $repoService = new RepositoryService($rootPath);
        $testRepo = $repoService->createRepository($repoName);
        
        $this->assertInstanceOf('\Gitonomy\Git\Repository', $testRepo);
    }
    
    public function testCreateExistingRepository()
    {
        $repoName = 'test';
        $rootPath = 'app/data';
        
        $repoPath = __DIR__ . '/../../../../' . $rootPath . '/' . $repoName;
        
        $fs = new Filesystem();
        $fs->mkdir($repoPath);
        
        $repo = \Gitonomy\Git\Admin::init($repoPath, false);
        
        $this->setExpectedException('\InvalidArgumentException');
        
        $repoService = new RepositoryService($rootPath);
        $testRepo = $repoService->createRepository($repoName);
    }
}
