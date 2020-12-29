<?php

namespace RocknRoot\StrayFw\Console;

/**
 * CLI route.
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
     * Route path.
     */
    private string $path;

    /**
     * Route usage text.
     */
    private string $usage;

    /**
     * Route help text.
     */
    private string $help;

    /**
     * Action to execute.
     */
    private string $action;

    /**
     * Action namespace.
     */
    private string $namespace;

    /**
     * Build a route to register it.
     *
     * @param string $kind route kind
     * @param string $path route path
     * @param string $usage route usage text
     * @param string $help route help text
     * @param string $action action
     * @param string $namespace namespace
     */
    public function __construct(string $kind, string $path, string $usage, string $help, string $action, string $namespace)
    {
        $this->kind = $kind;
        $this->path = $path;
        $this->usage = $usage;
        $this->help = $help;
        $this->action = $action;
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
     * Get path.
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Get usage text.
     *
     * @return string
     */
    public function getUsage(): string
    {
        return $this->usage;
    }

    /**
     * Get help text.
     *
     * @return string
     */
    public function getHelp(): string
    {
        return $this->help;
    }

    /**
     * Get action.
     *
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
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
