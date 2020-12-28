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
    protected \RocknRoot\StrayFw\Http\RawRequest $rawRequest;

    /**
     * Apps routes.
     *
     * @var array[]
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
     * @param array[]    $routes     registered routes
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
            if (isset($route['subdomain']) === true) {
                if (\in_array($this->rawRequest->getSubDomain(), $route['subdomain']) === false) {
                    continue;
                }
            }
            if (isset($route['path']) === false || isset($route['action']) === false) {
                throw new InvalidRouteDefinition('route "' . $route['path'] . '" has invalid definition');
            }
            foreach ($route['action'] as $r) {
                if (\stripos($r, '.') === false) {
                    throw new InvalidRouteDefinition('route "' . $route['path'] . '" has invalid definition');
                }
            }

            if (isset($route['method']) === false || \strtolower($route['method']) === 'all' || \strtolower($route['method']) == \strtolower($this->rawRequest->getMethod())) {
                $path = $route['path'];
                if (empty($route['uri']) === false) {
                    $path = '/' . \ltrim(\rtrim($route['uri'], '/'), '/') . $path;
                }
                if (\strlen($route['path']) > 1) {
                    $path = \rtrim($path, '/');
                }
                $matches = null;
                if ($route['type'] == 'before' || $route['type'] == 'after') {
                    if (\preg_match('#^' . $path . '#', $this->rawRequest->getQuery(), $matches) === 1) {
                        foreach ($route['action'] as $r) {
                            list($class, $action) = \explode('.', $r);
                            if (\stripos($class, '\\') !== 0 && isset($route['namespace']) === true) {
                                $class = \rtrim($route['namespace'], '\\') . '\\' . $class;
                            }
                            $a = [ 'class' => $class, 'action' => $action ];
                            if ($route['type'] == 'before') {
                                $this->before[] = $a;
                            } else {
                                $this->after[] = $a;
                            }
                        }
                    }
                } elseif (\count($this->actions) == 0) {
                    if (\preg_match('#^' . $path . '$#', $this->rawRequest->getQuery(), $matches) === 1) {
                        foreach ($route['action'] as $r) {
                            list($class, $action) = \explode('.', $r);
                            if (\stripos($class, '\\') !== 0 && isset($route['namespace']) === true) {
                                $class = \rtrim($route['namespace'], '\\') . '\\' . $class;
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
