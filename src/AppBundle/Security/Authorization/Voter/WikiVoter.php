<?php

namespace AppBundle\Security\Authorization\Voter;

use AppBundle\Service\RepositoryService;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Yaml\Parser;

class WikiVoter extends Voter
{
    const SHOW = 'show';
    const EDIT = 'edit';
    const DELETE = 'delete';
    
    protected $adminRepository;
    
    function __construct(RepositoryService $repositoryService, $adminRepository)
    {
        $this->adminRepository = $repositoryService->getRepository($adminRepository);
        
        $this->yamlParser = new Parser();
    }
    
    public function supports($attribute, $subject)
    {
        if (!in_array($attribute, array(self::SHOW, self::EDIT, self::DELETE))) {
            return false;
        }
        
        if (!is_string($subject)) {
            return false;
        }
        
        return true;
    }
    
    public function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $lastCommit = $this->adminRepository->getReferences()->getBranch('master')->getCommit();
        $wiki = false;
        $user = $token->getUser();
        
        try {
            $wikiDataString = $lastCommit->getTree()->resolvePath('wikis/' . $subject . '.yml');
            $wiki = $this->yamlParser->parse($wikiDataString->getContent());
        } catch (\Exception $exception) {
            $wiki = false;
        }
        
        if ($wiki === false) {
            throw new \InvalidArgumentException(sprintf('Wiki %s was not found in the admin repository.', $subject));
        }
        
        switch($attribute) {
            case self::SHOW:
                return $this->canShow($wiki, $user);
            case self::EDIT:
                return $this->canEdit($wiki, $user);
            case self::DELETE:
                return $this->canDelete($wiki, $user);
        }
        
        throw new \LogicException('This code should not be reached!');
    }
    
    private function canShow($wiki, UserInterface $user)
    {
        if ($this->canEdit($wiki, $user) === true) {
            return true;
        }
        
        $roles = $user->getRoles();
        $hasAccess = false;
        
        while($role = array_pop($roles)) {
            if ($wiki['groups'][$role] === 'R') {
                $hasAccess = true;
            }
        }
        
        return $hasAccess;
    }
    
    private function canEdit($wiki, UserInterface $user)
    {
        if ($this->canDelete($wiki, $user) === true) {
            return true;
        }
        
        $roles = $user->getRoles();
        $hasAccess = false;
        
        while($role = array_pop($roles)) {
            if ($wiki['groups'][$role] === 'RW') {
                $hasAccess = true;
            }
        }
        
        return $hasAccess;
    }
    
    private function canDelete($wiki, UserInterface $user)
    {
        $roles = $user->getRoles();
        $hasAccess = false;
        
        while($role = array_pop($roles)) {
            if ($wiki['groups'][$role] === 'RW+') {
                $hasAccess = true;
            }
        }
        
        return $hasAccess;
    }
}
