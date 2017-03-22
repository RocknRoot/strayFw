<?php

namespace RocknRoot\StrayFw\Http;

use RocknRoot\StrayFw\Exception\InvalidRouteDefinition;
use RocknRoot\StrayFw\Exception\RouteNotFound;

/**
 * Routed data from raw request.
 *
 * @author Nekith <nekith@errant-works.com>
 */
class Request
{
    /**
     * Raw request.
     *
     * @var RawRequest
     */
    protected $rawRequest;

    /**
     * Route class name.
     *
     * @param string
     */
    protected $class;

    /**
     * Route action name.
     *
     * @param string
     */
    protected $action;

    /**
     * Route parsed arguments.
     *
     * @param mixed[]
     */
    protected $args;

    /**
     * Matching before hooks.
     *
     * @param string[]
     */
    protected $before;

    /**
     * Matching after hooks.
     *
     * @param string[]
     */
    protected $after;

    /**
     * True if route needs to stop early.
     *
     * @param bool
     */
    protected $hasEnded;

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
            if (isset($route['subdomain']) === true && ($this->rawRequest->getSubDomain() != null && $route['subdomain'] != $this->rawRequest->getSubDomain())) {
                continue;
            }
            if (isset($route['path']) === false || isset($route['action']) === false || strpos($route['action'], '.') === false) {
                throw new InvalidRouteDefinition('route "' . $route['path'] . '" has invalid definition');
            }
            if (isset($route['method']) === false || strtolower($route['method']) === 'all' || strtolower($route['method']) == strtolower($this->rawRequest->getMethod())) {
                if (isset($route['ajax']) === false || $route['ajax'] == $this->rawRequest->isAjax()) {
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
                    } else {
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
    public function getRawRequest()
    {
        return $this->rawRequest;
    }

    /**
     * Get route class name.
     *
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Get route action name.
     *
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Get route parsed arguments.
     *
     * @param mixed[]
     */
    public function getArgs()
    {
        return $this->args;
    }

    /**
     * Matching before hooks.
     *
     * @param string[]
     */
    public function getBefore()
    {
        return $this->before;
    }

    /**
     * Matching after hooks.
     *
     * @param string[]
     */
    public function getAfter()
    {
        return $this->after;
    }

    /**
     * Set the request to end early.
     *
     * @return bool previous value
     */
    public function end()
    {
        $v = $this->hasEnded;
        $this->hasEnded = true;

        return $v;
    }

    /**
     * True if route needs to stop early.
     *
     * @return bool
     */
    public function hasEnded()
    {
        return $this->hasEnded;
    }

    /**
     * Retrieve an input var from, in this order of priority:
     *  * POST vars
     *  * route args
     *  * GET vars
     *  * $default
     *
     * @param string $name input searched
     * @param mixed $default returned value if nothing is found
     * @return mixed found value or default
     */
    public function input($name, $default = null)
    {
        if (isset($this->rawRequest->getPostVars()[$name]) === true) {
            return $this->rawRequest->getPostVars()[$name];
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
