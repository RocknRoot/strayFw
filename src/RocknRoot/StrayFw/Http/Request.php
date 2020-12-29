<?php

namespace RocknRoot\StrayFw\Http;

use RocknRoot\StrayFw\Exception\InvalidRouteDefinition;
use RocknRoot\StrayFw\Exception\RouteNotFound;
use RocknRoot\StrayFw\Request as BaseRequest;

/**
 * Routed data from raw request.
 *
 * @author Nekith <nekith@errant-works.com>
 */
class Request extends BaseRequest
{
    /**
     * Raw request.
     */
    protected RawRequest $rawRequest;

    /**
     * Apps routes.
     *
     * @var Route[]
     */
    protected array $routes;

    /**
     * Request data.
     *
     * @var array<string, mixed>
     */
    public array $data = [];

    /**
     * Construct request.
     *
     * @param RawRequest $rawRequest base raw request
     * @param Route[]    $routes     registered routes
     */
    public function __construct(RawRequest $rawRequest, array $routes)
    {
        $this->rawRequest = $rawRequest;
        $this->routes = $routes;
        if (\count($routes) == 0) {
            throw new InvalidRouteDefinition('there is no route');
        }
    }

    /**
     * Parse raw request and choose a route.
     *
     * @throws InvalidRouteDefinition if there is no route
     * @throws InvalidRouteDefinition if a route has an invalid definition
     * @throws RouteNotFound          if no route matches the request
     */
    public function route(): void
    {
        foreach ($this->routes as $route) {
            if (\count($route->getSubDomains()) >= 1) {
                if (\in_array($this->rawRequest->getSubDomain(), $route->getSubDomains()) === false) {
                    continue;
                }
            }
            if ($route->getPath() === '' || \count($route->getActions()) === 0) {
                throw new InvalidRouteDefinition('route "' . $route->getPath() . '" has invalid definition');
            }
            foreach ($route->getActions() as $r) {
                if (\stripos($r, '.') === false) {
                    throw new InvalidRouteDefinition('route "' . $route->getPath() . '" has invalid definition');
                }
            }

            if ($route->getMethod() === '' || \strtolower($route->getMethod()) === 'all' || \strtolower($route->getMethod()) == \strtolower($this->rawRequest->getMethod())) {
                $path = $route->getPath();
                if ($route->getURI() !== '') {
                    $path = '/' . \ltrim(\rtrim($route->getURI(), '/'), '/') . $path;
                }
                if (\strlen($route->getPath()) > 1) {
                    $path = \rtrim($path, '/');
                }
                $matches = null;
                if ($route->getKind() == 'before' || $route->getKind() == 'after') {
                    if (\preg_match('#^' . $path . '#', $this->rawRequest->getQuery(), $matches) === 1) {
                        foreach ($route->getActions() as $r) {
                            list($class, $action) = \explode('.', $r);
                            if (\stripos($class, '\\') !== 0 && $route->getNamespace() !== '') {
                                $class = \rtrim($route->getNamespace(), '\\') . '\\' . $class;
                            }
                            $a = [ 'class' => $class, 'action' => $action ];
                            if ($route->getKind() == 'before') {
                                $this->before[] = $a;
                            } else {
                                $this->after[] = $a;
                            }
                        }
                    }
                } elseif (\count($this->actions) == 0) {
                    if (\preg_match('#^' . $path . '$#', $this->rawRequest->getQuery(), $matches) === 1) {
                        foreach ($route->getActions() as $r) {
                            list($class, $action) = \explode('.', $r);
                            if (\stripos($class, '\\') !== 0 && $route->getNamespace() !== '') {
                                $class = \rtrim($route->getNamespace(), '\\') . '\\' . $class;
                            }
                            $a = [ 'class' => $class, 'action' => $action ];
                            $this->actions[] = $a;
                            foreach ($matches as $k => $v) {
                                if (\is_numeric($k) === false && $v != null) {
                                    $this->args[$k] = $v;
                                }
                            }
                        }
                    }
                }
            }
        }
        if (\count($this->actions) == 0) {
            throw new RouteNotFound('no route matches this : ' . \print_r($this->rawRequest, true));
        }
    }

    /**
     * Magic method for object cloning.
     */
    public function __clone()
    {
        $this->rawRequest = clone $this->rawRequest;
    }

    /**
     * Get associated raw request.
     *
     * @return RawRequest
     */
    public function getRawRequest(): RawRequest
    {
        return $this->rawRequest;
    }

    /**
     * Retrieve an input var from, in this order of priority:
     *  * POST vars
     *  * JSON body vars
     *  * route args
     *  * GET vars
     *  * $default
     *
     * @param  string $name    input searched
     * @param  mixed  $default returned value if nothing is found
     * @return mixed  found value or default
     */
    public function input(string $name, $default = null)
    {
        if (isset($this->rawRequest->getPostVars()[$name]) === true) {
            return $this->rawRequest->getPostVars()[$name];
        }
        if (isset($this->rawRequest->getJSONBodyVars()[$name]) === true) {
            return $this->rawRequest->getJSONBodyVars()[$name];
        }
        if (isset($this->args[$name]) === true) {
            return $this->args[$name];
        }
        if (isset($this->rawRequest->getGetVars()[$name]) === true) {
            return $this->rawRequest->getGetVars()[$name];
        }

        return $default;
    }
}
