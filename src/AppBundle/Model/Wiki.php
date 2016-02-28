<?php
/**
 * Created by PhpStorm.
 * User: mariusbuscher
 * Date: 25.02.16
 * Time: 07:48
 */

namespace AppBundle\Model;

use Gitonomy\Git\Tree;

class Wiki
{
    protected $slug;

    protected $repository;

    protected $branchName;

    public function __construct()
    {
        $this->branchName = 'master';
    }

    public function getPage($path = '')
    {
        $pageTree = $this->getBranch()
            ->getCommit()
            ->getTree();

        $pageObj = $pageTree->resolvePath($path);

        $name = ($path === '') ? '' : $path;

        $page = $page = new Page($this, $name);

        if ($pageObj instanceof Tree) {

            $page->setHasSubpages(true)
                ->setTree($pageObj)
                ->setPath($path)
                ->setBlob($pageObj->resolvePath('index.md'));
        } else {
            $page->setBlob($pageObj)
                ->setPath($path);
        }

        return $page;
    }

    public function getName()
    {
        return $this->getRepository()
            ->getDescription();
    }

    public function setName($name)
    {
        $this->getRepository()
            ->setDescription($name);

        return $this;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    public function getRepository()
    {
        return $this->repository;
    }

    public function setRepository($repository)
    {
        $this->repository = $repository;

        return $this;
    }

    public function getBranch()
    {
        return $this->getRepository()
            ->getReferences()
            ->getBranch($this->getBranchName());
    }

    /**
     * @return mixed
     */
    public function getBranchName()
    {
        return $this->branchName;
    }

    /**
     * @param mixed $branchName
     * @return self
     */
    public function setBranchName($branchName)
    {
        $this->branchName = $branchName;

        return $this;
    }

    public function __toString()
    {
        return $this->getSlug();
    }
}