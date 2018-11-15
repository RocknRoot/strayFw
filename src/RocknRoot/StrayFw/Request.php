<?php

namespace RocknRoot\StrayFw;

use RocknRoot\StrayFw\Exception\InvalidRouteDefinition;
use RocknRoot\StrayFw\Exception\RouteNotFound;

/**
 * Base class for requests.
 *
 * @author Nekith <nekith@errant-works.com>
 */
abstract class Request
{
    /**
     * Route class name.
     *
     * @var string
     */
    protected $class;

    /**
     * Route action name.
     *
     * @var string
     */
    protected $action;

    /**
     * Route parsed arguments.
     *
     * @var mixed[]
     */
    protected $args;

    /**
     * Matching before hooks.
     *
     * @var string[][]
     */
    protected $before;

    /**
     * Matching after hooks.
     *
     * @var string[][]
     */
    protected $after;

    /**
     * True if route needs to stop early.
     *
     * @var bool
     */
    protected $hasEnded;

    /**
     * Get route class name.
     *
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Get route action name.
     *
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Get route parsed arguments.
     *
     * @return mixed[] parsed arguments
     */
    public function getArgs()
    {
        return $this->args;
    }

    /**
     * Matching before hooks.
     *
     * @return string[][] matched hooks
     */
    public function getBefore()
    {
        return $this->before;
    }

    /**
     * Matching after hooks.
     *
     * @return string[][] matched hooks
     */
    public function getAfter()
    {
        return $this->after;
    }

    /**
     * Set the request to end early.
     *
     * @return bool previous value
     */
    public function end()
    {
        $v = $this->hasEnded;
        $this->hasEnded = true;

        return $v;
    }

    /**
     * True if route needs to stop early.
     *
     * @return bool
     */
    public function hasEnded()
    {
        return $this->hasEnded;
    }
}
