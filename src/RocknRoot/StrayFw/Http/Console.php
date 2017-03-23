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
        $table->setHeaders([ 'Type', 'Subdomain', 'Method', 'Path', 'Namespace', 'Action' ]);
        $row = [];
        $routes = Http::getRoutes();
        usort($routes, function(array $a, array $b) {
            if ($a['subdomain'] != $b['subdomain']) {
                return strcmp($a['subdomain'], $b['subdomain']);
            }
            return strcmp($a['path'], $b['path']);
        });
        foreach ($routes as $route) {
            $rows[] = [
                $route['type'],
                $route['subdomain'],
                $route['method'],
                empty($route['uri']) === false ? '/' . ltrim(rtrim($route['uri'], '/'), '/') . $route['path'] : $route['path'],
                $route['namespace'],
                $route['action'],
            ];
        }
        $table->setRows($rows);
        $table->display();
    }
}
