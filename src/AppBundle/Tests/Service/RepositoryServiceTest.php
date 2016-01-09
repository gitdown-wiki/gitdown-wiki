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
        $rootPath = realpath(__DIR__ . '/../../../../var/data');
        
        $fs = new Filesystem();
        
        $fs->remove(realpath($rootPath . '/' . $repoName));
    }
    
    public function testRepositoryGetter()
    {
        $repoName = 'test';
        $rootPath = realpath(__DIR__ . '/../../../../var/data');
        
        $repoPath = $rootPath . '/' . $repoName;
        
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
        $rootPath = realpath(__DIR__ . '/../../../../var/data');
        
        $repoPath = $rootPath . '/' . $repoName;
        
        $this->setExpectedException('\InvalidArgumentException');
        
        $repoService = new RepositoryService($rootPath);
        $testRepo = $repoService->getRepository($repoName);
    }
    
    public function testWithoutValidRepo()
    {
        $repoName = 'test';
        $rootPath = realpath(__DIR__ . '/../../../../var/data');
        
        $repoPath = $rootPath . '/' . $repoName;
        
        $fs = new Filesystem();
        $fs->mkdir($repoPath);
        
        $this->setExpectedException('\InvalidArgumentException');
        
        $repoService = new RepositoryService($rootPath);
        $testRepo = $repoService->getRepository($repoName);
    }
    
    public function testAllReposGetter()
    {
        $repoNames = array(
            'test',
            'test-2'
        );
        $rootPath = realpath(__DIR__ . '/../../../../var/data').'/test';
        
        $fs = new Filesystem();
        
        foreach ($repoNames as $repoName) {
            $repoPath = $rootPath . '/' . $repoName;
            $fs->mkdir($repoPath);
            $repo = \Gitonomy\Git\Admin::init($repoPath, false);
            $repo->setDescription($repoName);
        }
        
        $repoService = new RepositoryService($rootPath);
        $repos = $repoService->getAllRepositories();
        
        $this->assertCount(2, $repos);
    }
    
    public function testRepoFromAllReposGetter()
    {
        $repoName = 'test';
        $repoDescription = 'A test description';
        $rootPath = realpath(__DIR__ . '/../../../../var/data').'/test';
        
        $fs = new Filesystem();
        
        $repoPath = $rootPath . '/' . $repoName;
        $fs->mkdir($repoPath);
        $repo = \Gitonomy\Git\Admin::init($repoPath, false);
        $repo->setDescription($repoDescription);
        
        $repoService = new RepositoryService($rootPath);
        $repos = $repoService->getAllRepositories();
        
        $this->assertEquals($repoName, $repos[0]['slug']);
        $this->assertEquals($repoDescription, $repos[0]['name']);
    }
    
    public function testRepositoryCreation()
    {
        $repoName = 'test';
        $rootPath = realpath(__DIR__ . '/../../../../var/data');
        
        $repoService = new RepositoryService($rootPath);
        $testRepo = $repoService->createRepository($repoName);
        
        $this->assertInstanceOf('\Gitonomy\Git\Repository', $testRepo);
    }
    
    public function testCreateExistingRepository()
    {
        $repoName = 'test';
        $rootPath = realpath(__DIR__ . '/../../../../var/data');
        
        $repoPath = $rootPath . '/' . $repoName;
        
        $fs = new Filesystem();
        $fs->mkdir($repoPath);
        
        $repo = \Gitonomy\Git\Admin::init($repoPath, false);
        
        $this->setExpectedException('\InvalidArgumentException');
        
        $repoService = new RepositoryService($rootPath);
        $testRepo = $repoService->createRepository($repoName);
    }
}
