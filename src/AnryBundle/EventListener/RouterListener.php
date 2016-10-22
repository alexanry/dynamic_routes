<?php

namespace AnryBundle\EventListener;

use AnryBundle\Support\RoutePathCreator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\EventListener\RouterListener as BaseListener;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

class RouterListener extends BaseListener
{
    /**
     * @var RouteCollection
     */
    protected $router;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var RoutePathCreator
     */
    protected $pathCreator;

    function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        /*
         * Create path generator instance. Could be a factory for different URL patterns or injected to constructor
         * as dependency
         */
        $this->pathCreator = new RoutePathCreator();

        $this->router = $this->container->get('router');

        parent::__construct(
            new UrlMatcher($this->router->getRouteCollection(), new RequestContext()),
            new RequestStack()
        );

        $this->addDynamicRoutes($this->router->getRouteCollection());
    }

    /**
     * Dynamically add routes to route collection
     *
     * @param RouteCollection $collection
     */
    protected function addDynamicRoutes(RouteCollection $collection)
    {
        foreach ($collection->all() as $route) {
            if (null !== $route->getDefault('_controller') && strpos($route->getDefault('_controller'), ':')) {

                $collection->add(
                    'dynamic_' . ($collection->count() + 1),
                    new Route(
                        $this->pathCreator->createNewPathFromRoute($route),
                        $route->getDefaults(),
                        $route->getRequirements(),
                        $route->getOptions(),
                        $route->getHost(),
                        $route->getSchemes(),
                        $route->getMethods(),
                        $route->getCondition()
                    )
                );
            }
        }
    }
}
