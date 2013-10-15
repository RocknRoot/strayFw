<?php

namespace ErrantWorks\StrayFw\Render;

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
     * @return string content
     */
    public function render();
}
