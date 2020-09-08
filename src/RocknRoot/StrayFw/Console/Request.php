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
        \array_shift($cli);
        if (\count($cli) > 0) {
            $cmd = \ltrim(\rtrim($cli[0], '/'), '/');
            foreach ($routes as $route) {
                if (isset($route['path']) === false || isset($route['action']) === false || \strpos($route['action'], '.') === false) {
                    throw new InvalidRouteDefinition('route "' . $route['path'] . '" has invalid definition');
                }
                if ($route['type'] == 'before' || $route['type'] == 'after') {
                    if (\stripos($cmd, $route['path']) === 0) {
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
                } elseif (count($this->actions) == 0) {
                    if ($cmd == $route['path']) {
                        foreach ($route['action'] as $r) {
                            list($class, $action) = \explode('.', $r);
                            if (\stripos($class, '\\') !== 0 && isset($route['namespace']) === true) {
                                $class = \rtrim($route['namespace'], '\\') . '\\' . $class;
                            }
                            $a = [ 'class' => $class, 'action' => $action ];
                            $this->actions[] = $a;
                            \array_shift($cli);
                            if (\is_array($cli) === true) {
                                $this->args = $cli;
                            }
                        }
                    }
                }
            }
        }
        if (count($this->actions) == 0) {
            $this->fillWithDefaultRoute();
        }
    }

    /**
     * Fill internal variables with help/default CLI route.
     */
    private function fillWithDefaultRoute() : void
    {
        $this->actions[] = [
            'class' => 'RocknRoot\\StrayFw\\Console\\Controller',
            'action' => 'help',
        ];
        $this->args = array();
    }
}
