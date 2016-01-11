<?php

namespace AppBundle\Security\User;

use AppBundle\Security\User\GitUser;
use AppBundle\Service\RepositoryService;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Yaml\Parser;

class GitUserProvider implements UserProviderInterface
{
    protected $adminRepository;
    
    protected $yamlParser;
    
    function __construct(RepositoryService $repositoryService, $adminRepository)
    {
        $this->adminRepository = $repositoryService->getRepository($adminRepository);
        
        $this->yamlParser = new Parser();
    }
    
    public function loadUserByUsername($username)
    {
        $lastCommit = $this->adminRepository->getReferences()->getBranch('master')->getCommit();
        $userData = false;
        
        try {
            $userDataString = $lastCommit->getTree()->resolvePath('users/' . $username . '.yml');
            $userData = $this->yamlParser->parse($userDataString->getContent());
        } catch (\Exception $exception) {
            $userData = false;
        }

        if ($userData) {
            $username = $username;
            $password = $userData['password'];
            $salt = $userData['salt'];
            $roles = $userData['roles'];
            $email = $userData['email'];
            $name = $userData['name'];
            
            $user = new GitUser($username, $password, $salt, $roles, $email, $name);

            return $user;
        }

        throw new UsernameNotFoundException(
            sprintf('Username "%s" does not exist.', $username)
        );
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof GitUser) {
            throw new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported.', get_class($user))
            );
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass($class)
    {
        return $class === 'AppBundle\Security\User\GitUser';
    }
}