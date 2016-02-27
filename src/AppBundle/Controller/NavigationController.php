<?php
/**
 * Created by PhpStorm.
 * User: mariusbuscher
 * Date: 24.02.16
 * Time: 19:06
 */

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;


class NavigationController extends Controller
{
    /**
     * @Template("navigation/wikis.html.twig")
     */
    public function wikisAction()
    {
        $wikis = $this->get('app.wikis')->getAll();

        return array(
            'wikis' => $wikis
        );
    }

    /**
     * @Template("navigation/pages.html.twig")
     */
    public function pagesAction($slug)
    {
        $wiki = $this->get('app.wikis')->getWiki($slug);

        return array(
            'wiki' => $wiki
        );
    }
}