<?php

namespace ErrantWorks\StrayFw\Render;

/**
 * strayFw extensions for Twig.
 *
 * @abstract
 *
 * @author Nekith <nekith@errant-works.com>
 */
abstract class TwigHelper
{
    /**
     * Get nice URL for specified route.
     *
     * @static
     * @param string $route route name
     * @param array $args route arguments
     * @return string nice URL
     */
    public static function route($route, $args = array())
    {
        // TODO
    }

    public static function tr($key, $args = array())
    {
        // TODO
    }

    public static function url($url)
    {
        // TODO
    }

    public static function session($name)
    {
        // TODO
    }
}
