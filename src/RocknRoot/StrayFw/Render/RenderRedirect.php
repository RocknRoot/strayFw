<?php

namespace RocknRoot\StrayFw\Render;

use RocknRoot\StrayFw\Exception\BadUse;

/**
 * HTTP redirect render.
 *
 * @author Nekith <nekith@errant-works.com>
 */
class RenderRedirect implements RenderInterface
{
    /**
     * Return the generated display.
     *
     * @return string content
     */
    public function render(array $args)
    {
        if (isset($args['url']) === false) {
            throw new BadUse('RenderRedirect: expected "url" entry in args but it\'s unset');
        }
        header('Location: ' . $args['url']);

        return null;
    }
}
