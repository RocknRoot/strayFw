<?php

namespace RocknRoot\StrayFw\Render;

/**
 * Required interface for all render types.
 *
 * @author Nekith <nekith@errant-works.com>
 */
interface RenderInterface
{
    /**
     * Return the generated display.
     *
     * @param  mixed[] $args render data
     * @return string  content
     */
    public function render(array $args) : string;
}
