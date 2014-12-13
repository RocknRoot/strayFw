<?php

namespace RocknRoot\StrayFw\Render;

/**
 * HTTP redirect render.
 *
 * @author Nekith <nekith@errant-works.com>
 */
class RenderRedirect implements RenderInterface
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
        header('Location: ' . $this->args['url']);

        return null;
    }
}
