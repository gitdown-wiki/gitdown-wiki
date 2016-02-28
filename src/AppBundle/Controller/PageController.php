<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;

use AppBundle\Model\Page;
use AppBundle\Model\Wiki;

class PageController extends Controller
{
    /**
     * @Route("/{wiki}/edit/{path}", name="page_edit", requirements={
     *     "path": "[\d\w-_\/\.+@*]*"
     * }, defaults={
     *     "path": ""
     * }))
     * @ParamConverter("wiki", class="AppBundle\Model\Wiki")
     * @Method("GET")
     */
    public function editAction(Wiki $wiki, $path)
    {
        $this->denyAccessUnlessGranted('edit', $wiki->getSlug());

        $page = $wiki->getPage($path);
        
        return $this->render('page/edit.html.twig', array(
            'wiki' => $wiki,
            'page' => $page,
            'path' => $path
        ));
    }
    
    /**
     * @Route("/{wiki}/edit/{path}", name="page_update", requirements={
     *     "path": "[\d\w-_\/\.+@*]*"
     * }, defaults={
     *     "path": ""
     * }))
     * @ParamConverter("siki", class="AppBundle\Model\Wiki")
     * @Method("POST")
     */
    public function updateAction(Wiki $wiki, $path, Request $request)
    {
        $this->denyAccessUnlessGranted('edit', $wiki->getSlug());
        
        $content = $request->request->get('content');
        $message = $request->request->get('message');
        
        $user = $this->getUser();

        $page->setContent($content);
        $page->save($user, $message);
        
        return $this->redirectToRoute('page_show', array('slug' => $slug, 'path' => $page->getPath()));
    }
    
    /**
     * @Route("/{wiki}/new/{path}", name="page_new", requirements={
     *     "path": "[\d\w-_\/\.+@*]*"
     * }))
     * @ParamConverter("wiki", class="AppBundle\Model\Wiki")
     * @Method("GET")
     */
    public function newAction(Wiki $wiki, $path = '')
    {
        $this->denyAccessUnlessGranted('edit', $wiki->getSlug());
        
        return $this->render('page/new.html.twig', array(
            'wiki' => $wiki,
            'path' => $path
        ));
    }
    
    /**
     * @Route("/{wiki}/new/{path}", name="page_create", requirements={
     *     "path": "[\d\w-_\/\.+@*]*"
     * }))
     * @ParamConverter("wiki", class="AppBundle\Model\Wiki")
     * @Method("POST")
     */
    public function createAction(Wiki $wiki, $path = '', Request $request)
    {
        $this->denyAccessUnlessGranted('edit', $wiki->getSlug());

        $name = $request->request->get('page');
        $content = $request->request->get('content');
        $message = $request->request->get('message');

        $user = $this->getUser();

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
     * @Route("/{wiki}/delete/{path}", name="page_delete", requirements={
     *     "path": "[\d\w-_\/\.+@*]*"
     * }, defaults={
     *     "path": ""
     * }))
     * @ParamConverter("wiki", class="AppBundle\Model\Wiki")
     * @Method("GET")
     */
    public function deleteAction(Wiki $wiki, $path)
    {
        $this->denyAccessUnlessGranted('delete', $wiki->getSlug());
        
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
     * @Route("/{wiki}/{path}", name="page_show", requirements={
     *     "path": "[\d\w-_\/\.+@*]*"
     * }, defaults={
     *     "path": ""
     * }))
     * @ParamConverter("wiki", class="AppBundle\Model\Wiki")
     * @Method("GET")
     */
    public function showAction(Wiki $wiki, $path)
    {
        $this->denyAccessUnlessGranted('show', $wiki->getSlug());

        $page = $wiki->getPage($path);
        
        return $this->render('page/show.html.twig', array(
            'wiki' => $wiki,
            'page' => $page,
            'path' => $path
        ));
    }
}
