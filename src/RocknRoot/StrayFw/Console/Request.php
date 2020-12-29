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
     * @param  Route[]                $routes registered routes
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
                if ($route->getPath() === '' || $route->getAction() === '' || \strpos($route->getAction(), '.') === false) {
                    throw new InvalidRouteDefinition('route "' . $route->getPath() . '" has invalid definition');
                }
                if ($route->getKind() == 'before' || $route->getKind() == 'after') {
                    if (\stripos($cmd, $route->getPath()) === 0) {
                        foreach ($route->getAction() as $r) {
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
                    if ($cmd == $route->getPath()) {
                        list($class, $action) = \explode('.', $route->getAction());
                        if (\stripos($class, '\\') !== 0 && $route->getNamespace() !== '') {
                            $class = \rtrim($route->getNamespace(), '\\') . '\\' . $class;
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
        if (\count($this->actions) == 0) {
            $this->fillWithDefaultRoute();
        }
    }

    /**
     * Fill internal variables with help/default CLI route.
     */
    private function fillWithDefaultRoute(): void
    {
        $this->actions[] = [
            'class' => 'RocknRoot\\StrayFw\\Console\\Controller',
            'action' => 'help',
        ];
        $this->args = array();
    }
}
