<?php

namespace AnryBundle\Support;

use AnryBundle\Contracts\RoutePathCreatorContract;
use Symfony\Component\Routing\Route;

class RoutePathCreator implements RoutePathCreatorContract
{
    /**
     * Generate a route path from route controller
     *
     * @param Route $route
     * @return string
     */
    public function createNewPathFromRoute(Route $route)
    {
        $path = '';
        $routePath = $route->getPath();

        /*
         * Catch all :: and : method separators in routes
         */
        $controller = str_ireplace('::', ":", $route->getDefault('_controller'));
        list($class, $method) = explode(':', $controller);

        /*
         * Get rid of "Controller/" in route
         */
        $class = str_ireplace('controller\\', '', $class);

        /*
         * Check if we have locale in path and if it exists make it first route parameter
         */
        if (strpos($route->getPath(), '{_locale}')) {
            $path = '{_locale}/';
            $routePath = str_ireplace('{_locale}', '', $routePath);
        }

        $wildcard = $this->getWildcardParams($routePath);

        /*
         * Build the route path
         */
        $path .= str_ireplace('\\', '/', $class).'/'.str_ireplace('action', '', $method).$wildcard;

        return $path;
    }

    /**
     * Get all wildcard route params to support {slug} in routes
     *
     * @param $path
     * @return string
     */
    protected function getWildcardParams($path)
    {
        $result = '';

        preg_match_all('/\{.*\}/iU', $path, $wildcard, PREG_PATTERN_ORDER);

        if (!empty($wildcard[0])) {
            foreach ($wildcard[0] as $key => $value) {
                $result .='/'.$value;
            }
        }

        return $result;
    }
}