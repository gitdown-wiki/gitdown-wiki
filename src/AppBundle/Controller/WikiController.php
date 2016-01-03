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
     * @Route("/wiki/new", name="wiki_new")
     * @Method("GET")
     */
    public function newAction()
    {
        return $this->render('wiki/new.html.twig', array());
    }
    
    /**
     * @Route("/wiki/new", name="wiki_create")
     * @Method("POST")
     */
    public function createAction(Request $request)
    {
        $wiki = new Wiki();
        
        $name = $request->request->get('name');
        $wiki->setName($name);
        
        $slug = $request->request->get('slug');
        if (empty($slug)) {
            $slug = $this->get('slug')->slugify($name);
        }
        $wiki->setSlug($slug);
        
        $em = $this->getDoctrine()->getManager();
        
        $em->persist($wiki);
        $em->flush();
        
        $repositoryService = $this->get('app.repository');
        
        $repository = $repositoryService->createRepository($slug);
        
        $path = $repository->getWorkingDir();
        
        $fs = new Filesystem();
        $fs->dumpFile($path . '/index.md', '# ' . $name);
        
        $repository->run('add', array('-A'));
        $repository->run('commit', array('-m Initial commit', '--author="Gitdown wiki <wiki@example.com>"'));
        
        return $this->redirectToRoute('page_show', array('slug' => $slug));
    }
    
    /**
     * @Route("/wiki", name="wiki_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $wikiRepository = $this->getDoctrine()->getRepository('AppBundle:Wiki');
        
        $wikis = $wikiRepository->findAll();
        
        return $this->render('wiki/index.html.twig', array(
            'wikis' => $wikis
        ));
    }
}
