<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
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

        $slug = $request->request->get('slug');
        if (empty($slug)) {
            $slug = $this->get('slug')->slugify($name);
        }
        
        $repositoryService = $this->get('app.repository');
        
        $repository = $repositoryService->createRepository($slug);
        
        $path = $repository->getWorkingDir();
        
        $fs = new Filesystem();
        $fs->dumpFile($path . '/index.md', '# ' . $name);
        
        $repository->setDescription($name);
        
        $repository->run('add', array('-A'));
        $repository->run('commit', array('-m Initial commit', '--author="Gitdown wiki <wiki@example.com>"'));
        
        return $this->redirectToRoute('page_show', array('slug' => $slug));
    }
    
    /**
     * @Route("/edit/{slug}", name="wiki_edit")
     * @Method("GET")
     */
    public function editAction($slug)
    {
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
