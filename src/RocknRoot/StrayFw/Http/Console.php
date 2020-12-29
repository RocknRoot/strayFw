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
    public function routes(Request $request): void
    {
        $table = new \cli\Table();
        $table->setHeaders([ 'Type', 'Subdomain', 'Method', 'Path', 'Action' ]);
        $rows = [];
        $routes = Http::getRoutes();
        \usort($routes, function (Route $a, Route $b): int {
            foreach ($a->getSubDomains() as $asd) {
                foreach ($b->getSubDomains() as $bsd) {
                    if ($asd !== $bsd) {
                        return \strcmp($asd, $bsd);
                    }
                }
            }
            if ($a->getPath() != $a->getPath()) {
                return \strcmp($a->getPath(), $b->getPath());
            }

            return \strcmp($a->getMethod(), $b->getMethod());
        });
        foreach ($routes as $route) {
            $actions = [];
            foreach ($route->getActions() as $a) {
                if ($a[0] == '\\') {
                    $actions[] = $a;
                } else {
                    $actions[] = \rtrim($route->getNamespace(), '\\') . '\\' . $a;
                }
            }
            $rows[] = [
                $route->getKind(),
                \implode(', ', $route->getSubDomains()),
                $route->getMethod(),
                $route->getURI() !== '' ? '/' . \ltrim(\rtrim($route->getURI(), '/'), '/') . $route->getPath() : $route->getPath(),
                \implode(', ', $actions),
            ];
        }
        $table->setRows($rows);
        $table->display();
    }
}
