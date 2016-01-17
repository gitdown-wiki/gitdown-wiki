<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Dumper;
use AppBundle\Entity\Wiki;

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
     * @Route("/edit/{slug}", name="wiki_edit")
     * @Method("GET")
     */
    public function editAction($slug)
    {
        $this->denyAccessUnlessGranted('edit', $slug);

        $repository = $this->get('app.repository')->getRepository($slug);

        $wiki = array(
            'slug' => $slug,
            'name' => $repository->getDescription()
        );

        return $this->render('wiki/edit.html.twig', array(
            'wiki' => $wiki
        ));
    }

    /**
     * @Route("/edit/{slug}", name="wiki_update")
     * @Method("POST")
     */
    public function updateAction($slug, Request $request)
    {
        $this->denyAccessUnlessGranted('edit', $slug);

        $repository = $this->get('app.repository')->getRepository($slug);

        $newName = $request->request->get('name');

        $repository->setDescription($newName);

        return $this->redirectToRoute('page_show', array('slug' => $slug));
    }

    /**
     * @Route("/", name="wiki_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $wikis = $this->get('app.repository')->getAllRepositories();

        return $this->render('wiki/index.html.twig', array(
            'wikis' => $wikis
        ));
    }
}
