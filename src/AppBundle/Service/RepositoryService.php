<?php

namespace AppBundle\Service;

use \Gitonomy\Git\Repository;

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
        }
        
        $repository = new Repository($path, array(
            'debug' => false
        ));
        
        return $repository;
    }
}
