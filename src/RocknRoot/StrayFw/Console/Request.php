<?php

namespace RocknRoot\StrayFw\Console;

use RocknRoot\StrayFw\Exception\InvalidRouteDefinition;

/**
 * Routed data from CLI.
 *
 * @author Nekith <nekith@errant-works.com>
 */
class Request
{
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
     * Parse executed command and choose a route.
     *
     * @throws InvalidRouteDefinition if a route has an invalid definition
     * @param  array[]                $routes registered routes
     */
    public function __construct(array $routes)
    {
        $this->before = array();
        $this->after = array();
        $this->hasEnded = false;
        global $argv;
        $cli = $argv;
        array_shift($cli);
        if (count($cli) > 0) {
            $cmd = ltrim(rtrim($cli[0], '/'), '/');
            foreach ($routes as $route) {
                if (isset($route['path']) === false || isset($route['action']) === false || strpos($route['action'], '.') === false) {
                    throw new InvalidRouteDefinition('route "' . $routeName . '" in "' . $file['file'] . '" has invalid definition');
                }
                if ($route['type'] == 'before' || $route['type'] == 'after') {
                    if (stripos($cmd, $route['path']) === 0) {
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
                    if ($cmd == $route['path']) {
                        list($this->class, $this->action) = explode('.', $route['action']);
                        if (stripos($this->class, '\\') !== 0 && isset($route['namespace']) === true) {
                            $this->class = rtrim($route['namespace'], '\\') . '\\' . $this->class;
                        }
                        array_shift($cli);
                        $this->args = $cli;
                        if (is_array($this->args) === false) {
                            $this->args = array();
                        }
                    }
                }
            }
        }
        if ($this->class == null) {
            $this->fillWithDefaultRoute();
        }
    }

    /**
     * Fill internal variables with help/default CLI route.
     */
    private function fillWithDefaultRoute()
    {
        $this->class = 'RocknRoot\\StrayFw\\Console\\Controller';
        $this->action = 'help';
        $this->args = array();
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
}
