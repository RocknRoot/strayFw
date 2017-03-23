<?php

namespace RocknRoot\StrayFw\Http;

use RocknRoot\StrayFw\Console\Request;
use RocknRoot\StrayFw\Http\Http;

/**
 * Console actions for Http namespace.
 *
 * @author Nekith <nekith@errant-works.com>
 */
class Console
{
    /**
     * List registered routes.
     *
     * @param Request $request current CLI request
     */
    public function routes(Request $request)
    {
        $routes = Http::getRoutes();
        foreach ($routes as $route) {
            echo $route['type'] . ' ' . $route['method'] . ' ' . $route['path'] . ' => ' . $route['namespace'] . ' ' . $route['action'] . PHP_EOL;
        }
    }
}
