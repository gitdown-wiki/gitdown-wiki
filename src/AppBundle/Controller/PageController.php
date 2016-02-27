<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;

use AppBundle\Model\Page;

class PageController extends Controller
{
    /**
     * @Route("/{slug}/edit/{path}", name="page_edit", requirements={
     *     "path": "[\d\w-_\/\.+@*]*"
     * }, defaults={
     *     "path": ""
     * }))
     * @Method("GET")
     */
    public function editAction($slug, $path)
    {
        $this->denyAccessUnlessGranted('edit', $slug);

        $wiki = $this->get('app.wikis')
            ->getWiki($slug);

        $page = $wiki->getPage($path);
        
        return $this->render('page/edit.html.twig', array(
            'wiki' => $wiki,
            'page' => $page,
            'path' => $path
        ));
    }
    
    /**
     * @Route("/{slug}/edit/{path}", name="page_update", requirements={
     *     "path": "[\d\w-_\/\.+@*]*"
     * }, defaults={
     *     "path": ""
     * }))
     * @Method("POST")
     */
    public function updateAction($slug, $path, Request $request)
    {
        $this->denyAccessUnlessGranted('edit', $slug);

        $page = $this->get('app.wikis')
            ->getWiki($slug)
            ->getPage($path);
        
        $content = $request->request->get('content');
        $message = $request->request->get('message');
        
        $user = $this->getUser();

        $page->setContent($content);
        $page->save($user, $message);
        
        return $this->redirectToRoute('page_show', array('slug' => $slug, 'path' => $page->getPath()));
    }
    
    /**
     * @Route("/{slug}/new/{path}", name="page_new", requirements={
     *     "path": "[\d\w-_\/\.+@*]*"
     * }))
     * @Method("GET")
     */
    public function newAction($slug, $path = '')
    {
        $this->denyAccessUnlessGranted('edit', $slug);

        $wiki = $this->get('app.wikis')
            ->getWiki($slug);
        
        return $this->render('page/new.html.twig', array(
            'wiki' => $wiki,
            'path' => $path
        ));
    }
    
    /**
     * @Route("/{slug}/new/{path}", name="page_create", requirements={
     *     "path": "[\d\w-_\/\.+@*]*"
     * }))
     * @Method("POST")
     */
    public function createAction($slug, $path = '', Request $request)
    {
        $this->denyAccessUnlessGranted('edit', $slug);

        $name = $request->request->get('page');
        $content = $request->request->get('content');
        $message = $request->request->get('message');

        $user = $this->getUser();

        $wiki = $this->get('app.wikis')
            ->getWiki($slug);

        $page = new Page($wiki, $name);

        $pagePath = $path;
        $pagePath .= (strlen($pagePath) === 0) ? '' : '/';
        $pagePath .= $name;

        if (preg_match('/\.md$/', $name) === 0) {
            $pagePath .= '/index.md';
        }

        $page->setPath($pagePath)
            ->setContent($content);
        
        $page->save($user, $message);
        
        return $this->redirectToRoute('page_show', array('slug' => $slug, 'path' => $path ));
    }
    
    /**
     * @Route("/{slug}/delete/{path}", name="page_delete", requirements={
     *     "path": "[\d\w-_\/\.+@*]*"
     * }, defaults={
     *     "path": ""
     * }))
     * @Method("GET")
     */
    public function deleteAction($slug, $path)
    {
        $this->denyAccessUnlessGranted('delete', $slug);
        
        if ($path === '') {
            throw new \InvalidArgumentException('Index.md can not be deleted.');
        }

        $user = $this->getUser();

        $page = $this->get('app.wikis')
            ->getWiki($slug)
            ->getPage($path);
        
        $page->delete($user);
        
        return $this->redirectToRoute('page_show', array('slug' => $slug));
    }
    
    /**
     * @Route("/{slug}/{path}", name="page_show", requirements={
     *     "path": "[\d\w-_\/\.+@*]*"
     * }, defaults={
     *     "path": ""
     * }))
     * @Method("GET")
     */
    public function showAction($slug, $path)
    {
        $this->denyAccessUnlessGranted('show', $slug);

        $wiki = $this->get('app.wikis')
            ->getWiki($slug);

        $page = $wiki->getPage($path);
        
        return $this->render('page/show.html.twig', array(
            'wiki' => $wiki,
            'page' => $page,
            'path' => $path
        ));
    }
}
