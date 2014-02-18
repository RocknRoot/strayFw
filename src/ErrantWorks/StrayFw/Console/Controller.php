<?php

namespace ErrantWorks\StrayFw\Console;

use ErrantWorks\StrayFw\Config;

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
    public function helpAction(Request $request)
    {
        $files = Console::getRoutes();
        echo 'strayFw console help screen.' . PHP_EOL . 'Available actions :' . PHP_EOL . PHP_EOL;
        foreach ($files as $file) {
            $routes = Config::get(rtrim($file['dir'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ltrim($file['file'], DIRECTORY_SEPARATOR));
            foreach ($routes['routes'] as $route) {
                echo $route['path'] . PHP_EOL;
                if (isset($route['help']) != null) {
                    echo "\t" . $route['help'];
                }
                echo PHP_EOL;
            }
            echo PHP_EOL;
        }
    }
}
