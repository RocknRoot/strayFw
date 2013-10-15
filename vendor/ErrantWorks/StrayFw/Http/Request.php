<?php

namespace ErrantWorks\StrayFw\Http;

use ErrantWorks\StrayFw\Config;
use ErrantWorks\StrayFw\Exception\NoRouteMatches;
use ErrantWorks\StrayFw\Http\RawRequest;

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
     * Current route name.
     *
     * @param string
     */
    protected $route;

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
     * Parse raw request and choose a route.
     *
     * @param RawRequest $rawRequest base raw request
     * @param array[] $routeFiles registered route files
     */
    public function __construct(RawRequest $rawRequest, array $routeFiles)
    {
        $this->rawRequest = $rawRequest;
        foreach ($routeFiles as $file) {
            $routes = Config::get($file);
            if (empty($routes['sub_domain']) === false && $routes['sub_domain'] != $this->rawRequest->getSubDomain()) {
                continue;
            }
            foreach ($routes['routes'] as $routeName => $routeInfo) {
                if (isset($routeInfo['path']) === false || isset($routeInfo['action']) === false || strpos($routeInfo['action'], '.') === false) {
                    throw new InvalidRouteDefinition('route "' . $routeName . '" in "' . $file['fileName']
                        . '" has invalid definition');
                }
                if (isset($routeInfo['method']) === false || $routeInfo['method'] == $this->rawRequest->getMethod()) {
                    if (isset($routeInfo['ajax']) === false || $routeInfo['ajax'] == $this->rawRequest->isAjax()) {
                        $path = $routeInfo['path'];
                        if (empty($routes['uri']) === false) {
                            $path = '/' . ltrim(rtrim($routes['uri'], '/'), '/') . $path;
                        }
                        if (strlen($routeInfo['path']) > 1) {
                            $path = rtrim($path, '/') . '/';
                        }
                        $matches = null;
                        if (preg_match('#^' . $path . '$#', $this->rawRequest->getQuery(), $matches) === 1) {
                            $this->route = $routeName;
                            list($this->class, $this->action) = explode('.', $routeInfo['action']);
                            if (isset($routes['namespace']) === true) {
                                $this->class = rtrim($routes['namespace'], '\\') . '\\' . ltrim($this->class, '\\');
                            }
                            foreach ($matches as $k => $v) {
                                if (is_numeric($k) && $v != null) {
                                    $this->args[$k] = $v;
                                }
                            }
                        }
                    }
                }
            }
            if ($this->route != null) {
                break;
            }
        }
        if ($this->route == null) {
            throw new NoRouteMatches('no route matches this : ' . print_r($this->rawRequest, true));
        }
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
     * Get current route name.
     *
     * @return string
     */
    public function getRoute()
    {
        return $this->route;
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
}
