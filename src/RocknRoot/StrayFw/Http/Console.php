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
        $table = new \cli\Table();
        $table->setHeaders([ 'Type', 'Method', 'Path', 'Namespace', 'Action' ]);
        $row = [];
        $routes = Http::getRoutes();
        foreach ($routes as $route) {
            $rows[] = [
                $route['type'],
                $route['method'],
                $route['path'],
                $route['namespace'],
                $route['action'],
            ];
        }
        $table->setRows($rows);
        $table->display();
    }
}
