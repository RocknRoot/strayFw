<?php

namespace RocknRoot\StrayFw\Http;

use RocknRoot\StrayFw\Console\Request;

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
        $table->setHeaders([ 'Type', 'Subdomain', 'Method', 'Path', 'Action' ]);
        $rows = [];
        $routes = Http::getRoutes();
        usort($routes, function (array $a, array $b) {
            if ($a['subdomain'] != $b['subdomain']) {
                return strcmp($a['subdomain'], $b['subdomain']);
            }
            if ($a['path'] != $a['path']) {
                return strcmp($a['path'], $b['path']);
            }

            return strcmp($a['method'], $b['method']);
        });
        foreach ($routes as $route) {
            $rows[] = [
                $route['type'],
                $route['subdomain'],
                $route['method'],
                empty($route['uri']) === false ? '/' . ltrim(rtrim($route['uri'], '/'), '/') . $route['path'] : $route['path'],
                $route['action'][0] == '\\' ? $route['action'] : rtrim($route['namespace'], '\\') . '\\' . $route['action'],
            ];
        }
        $table->setRows($rows);
        $table->display();
    }
}
