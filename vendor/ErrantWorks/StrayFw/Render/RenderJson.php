<?php

namespace ErrantWorks\StrayFw\Render;

use ErrantWorks\StrayFw\Render\ArgsTrait;

/**
 * JSON render class, useful for AJAX requests.
 *
 * @author Nekith <nekith@errant-works.com>
 */
class RenderJson implements RenderInterface
{
    use ArgsTrait;

    /**
     * Construct render with base arguments.
     *
     * @param array $args base arguments
     */
    public function __construct(array $args = array())
    {
        $this->args = $args;
    }

    /**
     * Return the generated display.
     *
     * @return string content
     */
    public function render()
    {
        header('Content-type: application/json');
        return json_encode($this->args, JSON_PRETTY_PRINT);
    }
}
