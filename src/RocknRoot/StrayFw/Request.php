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
     * Route class name.
     */
    protected ?string $class = null;

    /**
     * Route action name.
     */
    protected ?string $action = null;

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
     * Get route class name.
     */
    public function getClass(): ?string
    {
        return $this->class;
    }

    /**
     * Get route action name.
     */
    public function getAction(): ?string
    {
        return $this->action;
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
