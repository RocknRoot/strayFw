<?php

namespace ErrantWorks\StrayFw\Http;

use ErrantWorks\StrayFw\Config;
use ErrantWorks\StrayFw\Exception\InvalidRouteDefinition;
use ErrantWorks\StrayFw\Exception\RouteNotFound;
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
     * Get current route base dir path.
     *
     * @param string
     */
    protected $dir;

    /**
     * Current route file name.
     *
     * @param string
     */
    protected $file;

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
     * @throws InvalidRouteDefinition if a route has an invalid definition
     * @throws RouteNotFound          if no route matches the request
     * @param  RawRequest             $rawRequest base raw request
     * @param  array[]                $routeFiles registered route files
     */
    public function __construct(RawRequest $rawRequest, array $routeFiles)
    {
        $this->rawRequest = $rawRequest;
        foreach ($routeFiles as $file) {
            $routes = Config::get(rtrim($file['dir'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ltrim($file['file'], DIRECTORY_SEPARATOR));
            if (empty($routes['sub_domain']) === false && $routes['sub_domain'] != $this->rawRequest->getSubDomain()) {
                continue;
            }
            foreach ($routes['routes'] as $routeName => $routeInfo) {
                if (isset($routeInfo['path']) === false || isset($routeInfo['action']) === false || strpos($routeInfo['action'], '.') === false) {
                    throw new InvalidRouteDefinition('route "' . $routeName . '" in "' . $file['file']
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
                            $this->dir = rtrim($file['dir'], DIRECTORY_SEPARATOR);
                            $this->file = DIRECTORY_SEPARATOR . ltrim($file['file'], DIRECTORY_SEPARATOR);
                            $this->route = $routeName;
                            list($this->class, $this->action) = explode('.', $routeInfo['action']);
                            if (isset($routes['namespace']) === true) {
                                $this->class = rtrim($routes['namespace'], '\\') . '\\' . ltrim($this->class, '\\');
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
            if ($this->route != null) {
                break;
            }
        }
        if ($this->route == null) {
            throw new RouteNotFound('no route matches this : ' . print_r($this->rawRequest, true));
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
     * Get current route base dir name.
     *
     * @return string
     */
    public function getDir()
    {
        return $this->dir;
    }

    /**
     * Get current route file name.
     *
     * @return string
     */
    public function getFile()
    {
        return $this->file;
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
