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
     * @param  mixed[] $args render data
     * @return string  content
     */
    public function render(array $args): string
    {
        if (isset($args['url']) === false) {
            throw new BadUse('RenderRedirect: expected "url" entry in args');
        }
        \header('Location: ' . $args['url']);
        return '';
    }
}
