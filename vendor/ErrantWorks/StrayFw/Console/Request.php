<?php

namespace ErrantWorks\StrayFw\Console;

use ErrantWorks\StrayFw\Config;
use ErrantWorks\StrayFw\Exception\InvalidRouteDefinition;

/**
 * Routed data from CLI.
 *
 * @author Nekith <nekith@errant-works.com>
 */
class Request
{
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
     * Command line arguments.
     *
     * @param mixed[]
     */
    protected $args;

    /**
     * Parse executed command and choose a route.
     *
     * @throws InvalidRouteDefinition if a route has an invalid definition
     * @param  array[]                $routeFiles registered route files
     */
    public function __construct(array $routeFiles)
    {
        global $argv;
        $cli = $argv;
        array_shift($cli);
        if (count($cli) > 0) {
            foreach ($routeFiles as $file) {
                $routes = Config::get(rtrim($file['dir'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ltrim($file['file'], DIRECTORY_SEPARATOR));
                foreach ($routes['routes'] as $routeName => $routeInfo) {
                    if (isset($routeInfo['path']) === false || isset($routeInfo['action']) === false || strpos($routeInfo['action'], '.') === false) {
                        throw new InvalidRouteDefinition('route "' . $routeName . '" in "' . $file['file'] . '" has invalid definition');
                    }
                    if ($cli[0] == $routeInfo['path']) {
                        $this->dir = rtrim($file['dir'], DIRECTORY_SEPARATOR);
                        $this->file = DIRECTORY_SEPARATOR . ltrim($file['file'], DIRECTORY_SEPARATOR);
                        $this->route = $routeName;
                        list($this->class, $this->action) = explode('.', $routeInfo['action']);
                        if (isset($routes['namespace']) === true) {
                            $this->class = rtrim($routes['namespace'], '\\') . '\\' . ltrim($this->class, '\\');
                        }
                        array_shift($cli);
                        $this->args = $cli;
                        if (is_array($this->args) === false) {
                            $this->args = array();
                        }
                    }
                    if ($this->route != null) {
                        break;
                    }
                }
                if ($this->route != null) {
                    break;
                }
            }
        }
        if ($this->route == null) {
            $this->fillWithDefaultRoute();
        }
    }

    /**
     * Fill internal variables with help/default CLI route.
     */
    private function fillWithDefaultRoute()
    {
        $this->dir = __DIR__;
        $this->file = DIRECTORY_SEPARATOR . 'console.yml';
        $this->route = 'console_help';
        $this->class = 'ErrantWorks\\StrayFw\\Console\\Controller';
        $this->action = 'help';
        $this->args = array();
    }

    /**
     * Get current route base dir path.
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
     * Command line arguments.
     *
     * @param mixed[]
     */
    public function getArgs()
    {
        return $this->args;
    }
}
