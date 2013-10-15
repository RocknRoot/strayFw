<?php

namespace ErrantWorks\StrayFw\Render;

/**
 * Generalized arguments container.
 *
 * @author Nekith <nekith@errant-works.com>
 */
trait ArgsTrait
{
    /**
     * Set args.
     *
     * @var mixed[]
     */
    protected $args = array();

    /**
     * True if arg is set.
     *
     * @param string $name arg name
     * @return bool
     */
    public function hasArg($name)
    {
        return isset($this->args[$name]);
    }

    /**
     * Get set arg.
     *
     * @param string $name arg name
     * @return mixed arg value
     */
    public function getArg($name)
    {
        if ($this->has($name) === false) {
            return null;
        }
        return $this->args[$name];
    }

    /**
     * Set arg.
     *
     * @param string $name arg name
     * @param mixed $value arg value
     */
    public function setArg($name, $value)
    {
        $this->args[$name] = $value;
    }
}
