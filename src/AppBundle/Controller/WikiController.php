<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Dumper;
use AppBundle\Model\Wiki;

class WikiController extends Controller
{
    /**
     * @Route("/new", name="wiki_new")
     * @Method("GET")
     */
    public function newAction()
    {
        return $this->render('wiki/new.html.twig', array());
    }

    /**
     * @Route("/new", name="wiki_create")
     * @Method("POST")
     */
    public function createAction(Request $request)
    {

        $name = $request->request->get('name');
        $user = $this->getUser();

        $slug = $request->request->get('slug');
        if (empty($slug)) {
            $slug = $this->get('slug')->slugify($name);
        }

        $repositoryService = $this->get('app.repository');

        $repository = $repositoryService->createRepository($slug);
        $adminRepository = $repositoryService->getRepository($this->getParameter('app.admin_repository'));

        $path = $repository->getWorkingDir();

        $fs = new Filesystem();
        $fs->dumpFile($path . '/index.md', '# ' . $name);

        $repository->setDescription($name);

        $repository->run('add', array('-A'));
        $repository->run('commit', array('-m Initial commit', '--author="'.$user->getName().' <'.$user->getEmail().'>"'));

        $username = $user->getUsername();

        $adminPath = $adminRepository->getWorkingDir();

        $yamlDumper = new Dumper();
        $yamlString = $yamlDumper->dump(array(
            'groups' => null,
            'owners' => array(
                $username
            ),
            'users' => array(
                $username => 'RW+'
            )
        ), 3);

        $fs->dumpFile($adminPath . '/wikis/' . $slug . '.yml', $yamlString);

        $adminMessage = sprintf('Added wiki %s', $slug);

        $adminRepository->run('add', array('-A'));
        $adminRepository->run('commit', array('-m ' . $adminMessage, '--author="'.$user->getName().' <'.$user->getEmail().'>"'));

        return $this->redirectToRoute('page_show', array('slug' => $slug));
    }

    /**
     * @Route("/edit/{wiki}", name="wiki_edit")
     * @ParamConverter("wiki", class="AppBundle\Model\Wiki")
     * @Method("GET")
     */
    public function editAction(Wiki $wiki)
    {
        $this->denyAccessUnlessGranted('edit', $wiki->getSlug());

        return $this->render('wiki/edit.html.twig', array(
            'wiki' => $wiki
        ));
    }

    /**
     * @Route("/edit/{wiki}", name="wiki_update")
     * @ParamConverter("wiki", class="AppBundle\Model\Wiki")
     * @Method("POST")
     */
    public function updateAction(Wiki $wiki, Request $request)
    {
        $this->denyAccessUnlessGranted('edit', $wiki->getSlug());

        $newName = $request->request->get('name');

        $wiki->setName($newName);

        return $this->redirectToRoute('page_show', array('wiki' => $wiki));
    }

    /**
     * @Route("/", name="wiki_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $wikis = $this->get('app.wikis')->getAll();

        return $this->render('wiki/index.html.twig', array(
            'wikis' => $wikis
        ));
    }
}
