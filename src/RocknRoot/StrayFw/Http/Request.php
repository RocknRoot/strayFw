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
     *
     * @var RawRequest
     */
    protected $rawRequest;

    /**
     * Request data.
     *
     * @var array
     */
    public $data;

    /**
     * Parse raw request and choose a route.
     *
     * @throws InvalidRouteDefinition if there is no route
     * @throws InvalidRouteDefinition if a route has an invalid definition
     * @throws RouteNotFound if no route matches the request
     * @param  RawRequest    $rawRequest base raw request
     * @param  array[]       $routes registered routes
     */
    public function __construct(RawRequest $rawRequest, array $routes)
    {
        $this->rawRequest = $rawRequest;
        $this->before = array();
        $this->after = array();
        $this->hasEnded = false;
        $this->data = [];
        if (count($routes) == 0) {
            throw new InvalidRouteDefinition('there is no route');
        }
        foreach ($routes as $route) {
            if (isset($route['subdomain']) === true) {
                if (in_array($this->rawRequest->getSubDomain(), $route['subdomain']) === false) {
                    continue;
                }
            }
            if (isset($route['path']) === false || isset($route['action']) === false || strpos($route['action'], '.') === false) {
                throw new InvalidRouteDefinition('route "' . $route['path'] . '" has invalid definition');
            }
            if (isset($route['method']) === false || strtolower($route['method']) === 'all' || strtolower($route['method']) == strtolower($this->rawRequest->getMethod())) {
                $path = $route['path'];
                if (empty($route['uri']) === false) {
                    $path = '/' . ltrim(rtrim($route['uri'], '/'), '/') . $path;
                }
                if (strlen($route['path']) > 1) {
                    $path = rtrim($path, '/');
                }
                $matches = null;
                if ($route['type'] == 'before' || $route['type'] == 'after') {
                    if (preg_match('#^' . $path . '#', $this->rawRequest->getQuery(), $matches) === 1) {
                        list($class, $action) = explode('.', $route['action']);
                        if (stripos($class, '\\') !== 0 && isset($route['namespace']) === true) {
                            $class = rtrim($route['namespace'], '\\') . '\\' . $class;
                        }
                        $a = [ 'class' => $class, 'action' => $action ];
                        if ($route['type'] == 'before') {
                            $this->before[] = $a;
                        } else {
                            $this->after[] = $a;
                        }
                    }
                } elseif ($this->class == null) {
                    if (preg_match('#^' . $path . '$#', $this->rawRequest->getQuery(), $matches) === 1) {
                        list($this->class, $this->action) = explode('.', $route['action']);
                        if (stripos($this->class, '\\') !== 0 && isset($route['namespace']) === true) {
                            $this->class = rtrim($route['namespace'], '\\') . '\\' . $this->class;
                        }
                        foreach ($matches as $k => $v) {
                            if (is_numeric($k) === false && $v != null) {
                                $this->args[$k] = $v;
                            }
                        }
                    }
                }
            }
        }
        if ($this->class == null) {
            throw new RouteNotFound('no route matches this : ' . print_r($this->rawRequest, true));
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
    public function getRawRequest() : RawRequest
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
     * @param string $name input searched
     * @param mixed $default returned value if nothing is found
     * @return mixed found value or default
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
