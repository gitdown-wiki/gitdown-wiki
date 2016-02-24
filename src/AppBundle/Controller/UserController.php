<?php

namespace AppBundle\Controller;

use AppBundle\Security\User\GitUser;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Dumper;

class UserController extends Controller
{

    /**
     * @Route("/user", name="user_index")
     */
    public function indexAction()
    {
        $adminRepository = $this->get('app.repository')
            ->getRepository($this->getParameter('app.admin_repository'));

        $yamlParser = new Parser();

        $usersTree = $adminRepository
            ->getReferences()
            ->getBranch('master')
            ->getCommit()
            ->getTree()
            ->resolvePath('users');

        $users = array();

        foreach ($usersTree->getEntries() as $userFile => $userBlob) {
            $userData = $yamlParser->parse($userBlob[1]->getContent());
            $username = strstr($userFile, '.yml', true);
            $user = new GitUser($username, '', '', $userData['roles'], $userData['email'], $userData['name']);
            array_push($users, $user);
        }

        return $this->render('user/index.html.twig', array(
            'users' => $users
        ));
    }

    /**
     * @Route("/user/new", name="user_new")
     * @Method("GET")
     */
    public function newAction()
    {
        return $this->render('user/new.html.twig');
    }

    /**
     * @Route("/user/new", name="user_create")
     * @Method("POST")
     */
    public function createAction(Request $request)
    {
        $username = $request->request->get('username');
        $password = $request->request->get('password');
        $name = $request->request->get('name');
        $email = $request->request->get('email');
        $roles = str_getcsv($request->request->get('username'));

        $curUser = $this->getUser();

        $adminRepositoryName = $this->getParameter('app.admin_repository');
        $adminRepository = $this->get('app.repository')->getRepository($adminRepositoryName);

        $user = $this->get('app.git_user_factory')->constructUser(
            $username,
            $password,
            $roles,
            $name,
            $email
        );

        $userArray = array(
            'roles' => $user->getRoles(),
            'password' => $user->getPassword(),
            'salt' => $user->getSalt(),
            'email' => $user->getEmail(),
            'name' => $user->getName()
        );

        $yamlDumper = new Dumper();
        $yaml = $yamlDumper->dump($userArray, 2);

        $fs = new Filesystem();
        $fs->dumpFile($adminRepository->getPath().'/users/'.$username.'.yml', $yaml);
        $fs->chmod($adminRepository->getPath().'/users/'.$username.'.yml', 0777);

        $message = sprintf('Added user %s.', $username);

        $adminRepository->run('add', array('-A'));
        $adminRepository->run('commit', array('-m ' . $message, '--author="'.$curUser->getName().' <'.$curUser->getEmail().'>"'));

        return $this->redirectToRoute('user_edit', array(
            'user' => $username
        ));
    }

    /**
     * @Route("/user/edit/{user}", name="user_edit")
     * @Method("GET")
     */
    public function editAction($user)
    {
        $adminRepositoryName = $this->getParameter('app.admin_repository');
        $adminRepository = $this->get('app.repository')->getRepository($adminRepositoryName);

        $branch = $adminRepository->getReferences()->getBranch('master');
        $commit = $branch->getCommit();
        $tree = $commit->getTree();
        $blob = $tree->resolvePath('users/' . $user . '.yml');

        $parser = new Parser();
        $userData = $parser->parse($blob->getContent());
        $userData['username'] = $user;

        return $this->render(
            'user/edit.html.twig',
            array(
                'user' => $userData
            )
        );
    }

    /**
     * @Route("/user/edit/{user}", name="user_update")
     * @Method("POST")
     */
    public function updateAction($user, Request $request)
    {
        $curUser = $this->getUser();

        $adminRepositoryName = $this->getParameter('app.admin_repository');
        $adminRepository = $this->get('app.repository')->getRepository($adminRepositoryName);

        $branch = $adminRepository->getReferences()->getBranch('master');
        $commit = $branch->getCommit();
        $tree = $commit->getTree();
        $blob = $tree->resolvePath('users/' . $user . '.yml');

        $parser = new Parser();
        $userData = $parser->parse($blob->getContent());
        $userData['username'] = $user;

        $userData['name'] = $request->request->get('name');
        $userData['email'] = $request->request->get('email');

        $yamlDumper = new Dumper();
        $yaml = $yamlDumper->dump($userData, 2);

        $fs = new Filesystem();
        $fs->dumpFile($adminRepository->getPath().'/users/'.$user.'.yml', $yaml);
        $fs->chmod($adminRepository->getPath().'/users/'.$user.'.yml', 0777);

        $message = sprintf('Updated user %s.', $user);

        $adminRepository->run('add', array('-A'));
        $adminRepository->run('commit', array('-m ' . $message, '--author="'.$curUser->getName().' <'.$curUser->getEmail().'>"'));

        return $this->redirectToRoute('user_edit', array(
            'user' => $user
        ));
    }

    /**
     * @Route("/user/delete/{user}", name="user_delete")
     * @Method("GET")
     */
    public function deleteAction($user)
    {
        $curUser = $this->getUser();

        $adminRepositoryName = $this->getParameter('app.admin_repository');
        $adminRepository = $this->get('app.repository')->getRepository($adminRepositoryName);

        $message = sprintf('Delete user %s', $user);

        $adminRepository->run('rm', array('users/'.$user . '.yml'));
        $adminRepository->run('commit', array('-m ' . $message, '--author="'.$curUser->getName().' <'.$curUser->getEmail().'>"'));

        return $this->redirectToRoute('user_index');
    }
}
