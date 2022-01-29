<?php

namespace RocknRoot\StrayFw;

/**
 * Base class for requests.
 *
 * @author Nekith <nekith@errant-works.com>
 */
abstract class Request
{
    /**
     * Route actions classes and names.
     *
     * @var string[][]
     */
    protected array $actions = [];

    /**
     * Route parsed arguments.
     *
     * @var mixed[]
     */
    protected array $args = [];

    /**
     * Matching before hooks.
     *
     * @var string[][]
     */
    protected array $before = [];

    /**
     * Matching after hooks.
     *
     * @var string[][]
     */
    protected array $after = [];

    /**
     * True if route needs to stop early.
     */
    protected bool $hasEnded = false;

    /**
     * Get route actions.
     *
     * @return string[][] matched routes
     */
    public function getActions(): array
    {
        return $this->actions;
    }

    /**
     * Get route parsed arguments.
     *
     * @return mixed[] parsed arguments
     */
    public function getArgs(): array
    {
        return $this->args;
    }

    /**
     * Matching before hooks.
     *
     * @return string[][] matched hooks
     */
    public function getBefore(): array
    {
        return $this->before;
    }

    /**
     * Matching after hooks.
     *
     * @return string[][] matched hooks
     */
    public function getAfter(): array
    {
        return $this->after;
    }

    /**
     * Set the request to end early.
     *
     * @return bool previous value
     */
    public function end(): bool
    {
        $v = $this->hasEnded;
        $this->hasEnded = true;
        return $v;
    }

    /**
     * True if route needs to stop early.
     */
    public function hasEnded(): bool
    {
        return $this->hasEnded;
    }
}
