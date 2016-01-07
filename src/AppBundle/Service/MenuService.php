<?php

namespace AppBundle\Service;

use Doctrine\ORM\EntityManager;

class MenuService
{
    protected $em;
    
    function __construct(EntityManager $em)
    {
        $this->em = $em;
    }
    
    public function getWikis()
    {
        $repository = $this->em->getRepository('AppBundle:Wiki');;
        
        $wikis = $repository->findAll();
        
        return $wikis;
    }
}