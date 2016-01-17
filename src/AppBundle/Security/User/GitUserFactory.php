<?php

namespace AppBundle\Security\User;

use Symfony\Component\DependencyInjection\ContainerInterface;

class GitUserFactory
{
    protected $container;

    function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function constructUser($username, $password, $roles, $name, $email)
    {
        $user = new GitUser($username, $password, '', $roles, $email, $name);

        $encoder = $this->container->get('security.password_encoder');
        $encodedPassword = $encoder->encodePassword($user, $password);

        return new GitUser($username, $encodedPassword, '', $roles, $email, $name);
    }

}
