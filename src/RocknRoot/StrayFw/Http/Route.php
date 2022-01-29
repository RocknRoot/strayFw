<?php

namespace RocknRoot\StrayFw\Http;

/**
 * HTTP route.
 *
 * @author Nekith <nekith@errant-works.com>
 */
class Route
{
    /**
     * Route kind.
     * Possible values: route, before, after.
     */
    private string $kind;

    /**
     * Route HTTP method.
     */
    private string $method;

    /**
     * Route path.
     */
    private string $path;

    /**
     * Route subDomains.
     *
     * @var array<string>
     */
    private array $subDomains;

    /**
     * Route URI.
     */
    private string $uri;

    /**
     * Actions to execute.
     *
     * @var array<string>
     */
    private array $actions;

    /**
     * Action namespace.
     */
    private string $namespace;

    /**
     * Build a route to register it.
     *
     * @param string   $kind       route kind
     * @param string   $method     route HTTP method
     * @param string   $path       route path
     * @param string[] $subDomains route subdomains
     * @param string   $uri        route URI
     * @param string[] $actions    route actions
     * @param string   $namespace  actions namespace
     */
    public function __construct(string $kind, string $method, string $path, array $subDomains, string $uri, array $actions, string $namespace)
    {
        $this->kind = $kind;
        $this->method = $method;
        $this->path = $path;
        $this->subDomains = $subDomains;
        $this->uri = $uri;
        $this->actions = $actions;
        $this->namespace = $namespace;
    }

    /**
     * Get kind.
     *
     * @return string
     */
    public function getKind(): string
    {
        return $this->kind;
    }

    /**
     * Get HTTP method.
     *
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Get path.
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Get subDomains.
     *
     * @return array<string>
     */
    public function getSubDomains(): array
    {
        return $this->subDomains;
    }

    /**
     * Get URI.
     *
     * @return string
     */
    public function getURI(): string
    {
        return $this->uri;
    }

    /**
     * Get actions.
     *
     * @return array<string>
     */
    public function getActions(): array
    {
        return $this->actions;
    }

    /**
     * Get action namespace.
     *
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }
}
