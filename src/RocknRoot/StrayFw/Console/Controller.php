<?php

namespace RocknRoot\StrayFw\Console;

/**
 * Basic actions for console.
 *
 * @author Nekith <nekith@errant-works.com>
 */
class Controller
{
    /**
     * Basic help action listing available all console actions.
     *
     * @param Request $request current request
     */
    public function help(Request $request): void
    {
        $routes = Console::getRoutes();
        \cli\line('strayFw console help screen%nAvailable actions :%n%n');
        $namespace = null;
        foreach ($routes as $route) {
            if ($namespace != $route['namespace']) {
                $namespace = $route['namespace'];
                \cli\line($namespace . '%n');
            }
            \cli\line('    %Y' . $route['usage'] . '%n');
            if (isset($route['help']) != null) {
                \cli\line('        %C' . $route['help']);
            }
            \cli\line('%W%n');
        }
    }
}
