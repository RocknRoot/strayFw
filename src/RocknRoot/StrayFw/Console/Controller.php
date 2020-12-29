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
        \cli\line('strayFw console help screen. Available actions :' . PHP_EOL);
        $namespace = null;
        foreach ($routes as $route) {
            if ($namespace != $route->getNamespace()) {
                $namespace = $route->getNamespace();
                \cli\line($namespace . '%n');
            }
            \cli\line('    %Y' . $route->getUsage() . '%n');
            if ($route->getHelp() !== '') {
                \cli\line('        %C' . $route->getHelp());
            }
            \cli\line('%W%n');
        }
    }
}
