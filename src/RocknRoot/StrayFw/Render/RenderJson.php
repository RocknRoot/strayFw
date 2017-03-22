<?php

namespace RocknRoot\StrayFw\Render;

/**
 * JSON render class, useful for AJAX requests.
 *
 * @author Nekith <nekith@errant-works.com>
 */
class RenderJson implements RenderInterface
{
    /**
     * Return the generated display.
     *
     * @return string content
     */
    public function render(array $args)
    {
        header('Content-type: application/json');
        if (STRAY_ENV === 'development') {
            return json_encode($args, JSON_PRETTY_PRINT);
        }

        return json_encode($args);
    }
}
