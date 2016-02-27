<?php
/**
 * Created by PhpStorm.
 * User: mariusbuscher
 * Date: 25.02.16
 * Time: 08:10
 */

namespace AppBundle\Service;

use AppBundle\Model\Wiki;


class WikiService
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function getWiki($slug)
    {
        $wiki = new Wiki();

        $wiki->setSlug($slug)
            ->setRepository(
                $this->container->get('app.repository')
                    ->getRepository($slug)
            );

        return $wiki;
    }

    public function getAll()
    {
        $wikis = array();

        $repos = $this->getContainer()->get('app.repository')
            ->getAllRepositories();

        foreach($repos as $repo) {
            $wiki = $this->getWiki($repo['slug']);
            array_push($wikis, $wiki);
        }

        return $wikis;
    }

    /**
     * @return mixed
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @param mixed $container
     * @return self
     */
    public function setContainer($container)
    {
        $this->container = $container;

        return $this;
    }


}