<?php

namespace RocknRoot\StrayFw\Console;

use RocknRoot\StrayFw\Config;

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
    public function help(Request $request)
    {
        $routes = Console::getRoutes();
        echo 'strayFw console help screen' . PHP_EOL . 'Available actions :' . PHP_EOL . PHP_EOL;
        foreach ($routes as $route) {
            echo $route['usage'] . PHP_EOL;
            if (isset($route['help']) != null) {
                echo '    ' . $route['help'];
            }
            echo PHP_EOL . PHP_EOL;
        }
    }
}
