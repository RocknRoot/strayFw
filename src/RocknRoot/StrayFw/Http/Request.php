<?php

namespace RocknRoot\StrayFw\Http;

use RocknRoot\StrayFw\Exception\InvalidRouteDefinition;
use RocknRoot\StrayFw\Exception\RouteNotFound;
use RocknRoot\StrayFw\Request as BaseRequest;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

/**
 * Routed data from HTTP request.
 *
 * @author Nekith <nekith@errant-works.com>
 */
class Request extends BaseRequest
{
    /**
     * HTTP request.
     */
    protected HttpRequest $httpRequest;

    /**
     * Apps routes.
     *
     * @var Route[]
     */
    protected array $routes;

    /**
     * Request data.
     *
     * @var array<string, mixed>
     */
    public array $data = [];

    /**
     * Construct request.
     *
     * @param HttpRequest $httpRequest base HTTP request
     * @param Route[]     $routes      registered routes
     */
    public function __construct(HttpRequest $httpRequest, array $routes)
    {
        $this->httpRequest = $httpRequest;
        $this->routes = $routes;
        if (\count($routes) == 0) {
            throw new InvalidRouteDefinition('there is no route');
        }
    }

    /**
     * Parse HTTP request and choose a route.
     *
     * @throws InvalidRouteDefinition if there is no route
     * @throws InvalidRouteDefinition if a route has an invalid definition
     * @throws RouteNotFound          if no route matches the request
     */
    public function route(): void
    {
        $subdomain = $this->httpRequest->getHost();
        if (\preg_match("/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i", $subdomain, $matches)) {
            $subdomain = $matches['domain'];
        }
        $subdomain = \rtrim((string)\strstr($this->httpRequest->getHost(), $subdomain, true), '.');
        foreach ($this->routes as $route) {
            if (\count($route->getSubDomains()) >= 1) {
                if (\in_array($subdomain, $route->getSubDomains()) === false) {
                    continue;
                }
            }
            if ($route->getPath() === '' || \count($route->getActions()) === 0) {
                throw new InvalidRouteDefinition('route "' . $route->getPath() . '" has invalid definition');
            }
            foreach ($route->getActions() as $r) {
                if (\stripos($r, '.') === false) {
                    throw new InvalidRouteDefinition('route "' . $route->getPath() . '" has invalid definition');
                }
            }

            if ($route->getMethod() === '' || \strtolower($route->getMethod()) === 'all' || \strtolower($route->getMethod()) == \strtolower($this->httpRequest->getMethod())) {
                $path = $route->getPath();
                if ($route->getURI() !== '') {
                    $path = '/' . \ltrim(\rtrim($route->getURI(), '/'), '/') . $path;
                }
                if (\strlen($route->getPath()) > 1) {
                    $path = \rtrim($path, '/');
                }
                $matches = null;
                if ($route->getKind() == 'before' || $route->getKind() == 'after') {
                    if (\preg_match('#^' . $path . '#', $this->httpRequest->getPathInfo(), $matches) === 1) {
                        foreach ($route->getActions() as $r) {
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
                    if (\preg_match('#^' . $path . '$#', $this->httpRequest->getPathInfo(), $matches) === 1) {
                        foreach ($route->getActions() as $r) {
                            list($class, $action) = \explode('.', $r);
                            if (\stripos($class, '\\') !== 0 && $route->getNamespace() !== '') {
                                $class = \rtrim($route->getNamespace(), '\\') . '\\' . $class;
                            }
                            $a = [ 'class' => $class, 'action' => $action ];
                            $this->actions[] = $a;
                            foreach ($matches as $k => $v) {
                                if (\is_numeric($k) === false && $v != null) {
                                    $this->args[$k] = $v;
                                }
                            }
                        }
                    }
                }
            }
        }
        if (\count($this->actions) == 0) {
            throw new RouteNotFound('no route matches this : ' . \print_r($this->httpRequest, true));
        }
    }

    /**
     * Magic method for object cloning.
     */
    public function __clone()
    {
        $this->httpRequest = clone $this->httpRequest;
    }

    /**
     * Get associated HTTP request.
     *
     * @return HttpRequest
     */
    public function getHttpRequest(): HttpRequest
    {
        return $this->httpRequest;
    }

    /**
     * Retrieve an input var from, in this order of priority:
     *  * route args
     *  * POST vars
     *  * GET vars
     *  * $default
     *
     * @param  string $name    input searched
     * @param  mixed  $default returned value if nothing is found
     * @return mixed  found value or default
     */
    public function input(string $name, $default = null)
    {
        if (isset($this->args[$name]) === true) {
            return $this->args[$name];
        }
        if ($this->httpRequest->request->has($name)) {
            return $this->httpRequest->request->get($name);
        }
        if ($this->httpRequest->query->has($name)) {
            return $this->httpRequest->query->get($name);
        }
        return $default;
    }
}
