<?php

namespace RocknRoot\StrayFw\Render;

/**
 * JSON render class.
 * In development environment, JSON is pretty printed.
 *
 * @author Nekith <nekith@errant-works.com>
 */
class RenderJson implements RenderInterface
{
    /**
     * Return the generated display.
     *
     * @param  array  $args        view args
     * @param  bool   $prettyPrint force pretty print parameter
     * @return string content
     */
    public function render(array $args, bool $prettyPrint = null)
    {
        header('Content-type: application/json');
        if ((constant('STRAY_ENV') === 'development' && $prettyPrint !== false) || $prettyPrint === true) {
            return json_encode($args, JSON_PRETTY_PRINT);
        }

        return json_encode($args);
    }
}
