<?php

namespace RocknRoot\StrayFw\Console;

use RocknRoot\StrayFw\Exception\InvalidRouteDefinition;
use RocknRoot\StrayFw\Request as BaseRequest;

/**
 * Routed data from CLI.
 *
 * @author Nekith <nekith@errant-works.com>
 */
class Request extends BaseRequest
{
    /**
     * Parse executed command and choose a route.
     *
     * @param  array[]                $routes registered routes
     * @throws InvalidRouteDefinition if a route has an invalid definition
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
                    throw new InvalidRouteDefinition('route "' . $route['path'] . '" has invalid definition');
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
    private function fillWithDefaultRoute() : void
    {
        $this->class = 'RocknRoot\\StrayFw\\Console\\Controller';
        $this->action = 'help';
        $this->args = array();
    }
}
