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
        $path = realpath(__DIR__ . '../../../' . $this->rootPath . '/' . $repositoryPath);
        $repository = new Repository($path, array(
            'debug' => false
        ));
        
        return $repository;
    }
}
