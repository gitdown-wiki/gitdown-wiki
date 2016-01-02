<?php

namespace AppBundle\Service;

use \Gitonomy\Git\Repository;
use Symfony\Component\Filesystem\Filesystem;

class RepositoryService
{
    protected $rootPath;
    
    function __construct($rootPath)
    {
        $this->rootPath = $rootPath;
    }
    
    public function getRepository($repositoryPath)
    {
        $path = realpath(__DIR__ . '/../../../' . $this->rootPath . '/' . $repositoryPath);
        
        if ($path === false) {
            throw new \InvalidArgumentException(sprintf('Repository "%s" does not exist.', $repositoryPath));
        } else if (\Gitonomy\Git\Admin::isValidRepository($path) === false) {
            throw new \InvalidArgumentException(sprintf('Repository "%s" is not a valid git repository.', $repositoryPath));
        }
        
        $repository = new Repository($path, array(
            'debug' => false
        ));
        
        return $repository;
    }
    
    public function createRepository($repositoryPath)
    {
        $path = __DIR__ . '/../../../' . $this->rootPath . '/' . $repositoryPath;
        
        if (realpath($path) === false) {
            $fs = new Filesystem();
            $fs->mkdir($path);
        } else if (\Gitonomy\Git\Admin::isValidRepository($path)) {
            throw new \InvalidArgumentException(sprintf('Repository "%s" already exists', $repositoryPath));
        }
        
        $repository = \Gitonomy\Git\Admin::init($path, false);
        
        return $repository;
    }
}
