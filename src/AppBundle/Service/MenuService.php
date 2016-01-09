<?php

namespace AppBundle\Service;

class MenuService
{
    protected $repositoryService;
    
    function __construct(RepositoryService $repositoryService)
    {
        $this->repositoryService = $repositoryService;
    }
    
    public function getWikis()
    {
        $wikis = $this->repositoryService->getAllRepositories();
        
        return $wikis;
    }
}