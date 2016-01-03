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
        
        return $this->redirectToRoute('wiki_showpage', array('slug' => $slug));
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
    
    /**
     * @Route("/wiki/{slug}/edit/{page}", name="wiki_editpage", requirements={
     *     "page": "[\d\w-_\/\.+@*]+"
     * }, defaults={
     *     "page": "index"
     * }))
     * @Method("GET")
     */
    public function editPageAction($slug, $page)
    {
        $wikiRepository = $this->getDoctrine()->getRepository('AppBundle:Wiki');
        $wiki = $wikiRepository->findOneBySlug($slug);
        
        $repository = $this->get('app.repository')->getRepository($slug);
        $branch = $repository->getReferences()->getBranch('master');
        $commit = $branch->getCommit();
        $tree = $commit->getTree();
        $blob = $tree->resolvePath($page . '.md');
        
        return $this->render('wiki/editPage.html.twig', array(
            'wiki' => $wiki,
            'tree' => $tree->getEntries(),
            'content' => $blob->getContent(),
            'path' => $page
        ));
    }
    
    /**
     * @Route("/wiki/{slug}/edit/{page}", name="wiki_updatepage", requirements={
     *     "page": "[\d\w-_\/\.+@*]+"
     * }, defaults={
     *     "page": "index"
     * }))
     * @Method("POST")
     */
    public function updatePageAction($slug, $page, Request $request)
    {
        $wikiRepository = $this->getDoctrine()->getRepository('AppBundle:Wiki');
        $wiki = $wikiRepository->findOneBySlug($slug);
        
        $repository = $this->get('app.repository')->getRepository($slug);
        
        $path = $repository->getWorkingDir();
        
        $content = $request->request->get('content');
        
        $message = $request->request->get('message');
        
        if (strlen($message) === 0) {
            $message = 'Update page ' . $page . '.md';
        }
        
        $fs = new Filesystem();
        $fs->dumpFile($path . '/' . $page . '.md', $content);
        
        $repository->run('add', array('-A'));
        $repository->run('commit', array('-m ' . $message, '--author="Gitdown wiki <wiki@example.com>"'));
        
        return $this->redirectToRoute('wiki_showpage', array('slug' => $slug, 'page' => $page));
    }
    
    /**
     * @Route("/wiki/{slug}/{page}", name="wiki_showpage", requirements={
     *     "page": "[\d\w-_\/\.+@*]+"
     * }, defaults={
     *     "page": "index"
     * }))
     * @Method("GET")
     */
    public function showPageAction($slug, $page)
    {
        $wikiRepository = $this->getDoctrine()->getRepository('AppBundle:Wiki');
        $wiki = $wikiRepository->findOneBySlug($slug);
        
        $repository = $this->get('app.repository')->getRepository($slug);
        $branch = $repository->getReferences()->getBranch('master');
        $commit = $branch->getCommit();
        $tree = $commit->getTree();
        $blob = $tree->resolvePath($page . '.md');
        
        return $this->render('wiki/showPage.html.twig', array(
            'wiki' => $wiki,
            'tree' => $tree->getEntries(),
            'content' => $blob->getContent(),
            'path' => $page
        ));
    }
}
