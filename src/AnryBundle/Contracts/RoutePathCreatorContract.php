<?php

namespace AnryBundle\Contracts;

use Symfony\Component\Routing\Route;

interface RoutePathCreatorContract
{
    public function createNewPathFromRoute(Route $route);
}