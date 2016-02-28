<?php
/**
 * Created by PhpStorm.
 * User: mariusbuscher
 * Date: 28.02.16
 * Time: 20:10
 */

namespace AppBundle\Request\ParamConverter;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;


class WikiConverter implements ParamConverterInterface
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function apply(Request $request, ParamConverter $configuration)
    {
        $name = $configuration->getName();
        $attribute = $request->attributes->get($name);

        $wiki = $this->container
            ->get('app.wikis')
            ->getWiki($attribute);

        $request->attributes->set($name, $wiki);

        return true;
    }

    public function supports(ParamConverter $configuration)
    {
        if (null === $this->container) {
            return false;
        }

        if ($configuration->getClass() !== 'AppBundle\Model\Wiki') {
            return false;
        }

        return true;
    }

}