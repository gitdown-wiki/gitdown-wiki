<?php
/**
 * Created by PhpStorm.
 * User: mariusbuscher
 * Date: 25.02.16
 * Time: 07:50
 */

namespace AppBundle\Model;

use Gitonomy\Git\Tree;
use Symfony\Component\Filesystem\Filesystem;


class Page
{
    protected $wiki;

    protected $name;

    protected $hasSubpages;

    protected $blob;

    protected $tree;

    protected $content;

    protected $path;

    public function __construct($wiki, $name)
    {
        $this->wiki = $wiki;
        $this->name = $name;
        $this->hasSubpages = false;
    }

    /**
     * @return mixed
     */
    public function getTree()
    {
        return $this->tree;
    }

    /**
     * @param mixed $tree
     * @return self
     */
    public function setTree($tree)
    {
        $this->tree = $tree;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getBlob()
    {
        return $this->blob;
    }

    /**
     * @param mixed $blob
     * @return self
     */
    public function setBlob($blob)
    {
        $this->blob = $blob;

        return $this;
    }

    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    public function getContent()
    {
        if (!empty($this->content)) {
            return $this->content;
        }

        return $this
            ->getBlob()
            ->getContent();
    }

    public function getPages()
    {

        $pages = array();

        if ($this->getTree() instanceof Tree) {

            foreach ($this->getTree()->getEntries() as $name => $pageObj) {
                $page = new Page($this->getWiki(), $name);

                if ($name === 'index.md') {
                    continue;
                }

                if ($pageObj[1] instanceof Tree) {
                    $path = $this->getPath();
                    $path .= (strlen($path) > 0) ? '/' : '';
                    $path .= $name;

                    $page->setHasSubpages(true)
                        ->setTree($pageObj[1])
                        ->setPath($path)
                        ->setBlob($pageObj[1]->resolvePath('index.md'));
                } else {
                    $page->setBlob($pageObj[1])
                        ->setPath(
                            $this->getPath() . '/' . $name
                        );
                }

                array_push($pages, $page);
            }
        }

        return $pages;
    }

    public function getWiki()
    {
        return $this->wiki;
    }

    public function setWiki($wiki)
    {
        $this->wiki = $wiki;

        return $this;
    }

    public function save($user, $message = '')
    {
        $repository = $this->getWiki()
            ->getRepository();

        $path = $repository->getWorkingDir();

        $pagePath = ($this->getHasSubpages() === true) ? $this->getPath() . '/index.md' : $this->getPath();

        $fs = new Filesystem();
        $fs->dumpFile($path . '/' . $pagePath , $this->getContent());

        if (strlen($message) === 0 && !empty($this->getBlob())) {
            $message = 'Update page ' . $this->getPath();
        } else if (strlen($message) === 0 && empty($this->getBlob())) {
            $message = 'Create page ' . $this->getPath();
        }

        $name = (!empty($user)) ? $user->getName() : 'Gitdown Wiki';
        $email = (!empty($user)) ? $user->getEmail() : 'wiki@example.com';

        $repository->run('add', array('-A'));
        $repository->run('commit', array('-m ' . $message, '--author="'.$name.' <'.$email.'>"'));

        return $this;
    }

    public function delete($user, $message = '')
    {
        $repository = $this->getWiki()
            ->getRepository();

        if (strlen($message) === 0) {
            $message = 'Delete page ' . $this->getPath();
        }

        $name = (!empty($user)) ? $user->getName() : 'Gitdown Wiki';
        $email = (!empty($user)) ? $user->getEmail() : 'wiki@example.com';

        $this->remove();
        $repository->run('commit', array('-m ' . $message, '--author="'.$name.' <'.$email.'>"'));
    }

    public function remove()
    {
        $repository = $this->getWiki()
            ->getRepository();

        if ($this->getHasSubpages() === true) {
            $repository->run('rm', array($this->getPath() . '/index.md'));
            foreach ($this->getPages() as $page) {
                $page->remove();
            }
        } else {
            $repository->run('rm', array($this->getPath()));
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getHasSubpages()
    {
        return $this->hasSubpages;
    }

    /**
     * @param boolean $hasSubpages
     * @return self
     */
    public function setHasSubpages($hasSubpages)
    {
        $this->hasSubpages = $hasSubpages;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param mixed $path
     * @return self
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }
}