<?php

namespace ErrantWorks\StrayFw\Render;

use ErrantWorks\StrayFw\Http\Helper as HttpHelper;

/**
 * Proxy class for Twig additional functions.
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
     * @param  Request $request current request
     * @param  string  $route   route name
     * @param  array   $args    route arguments
     * @return string  nice URL
     */
    public static function route(Request $request, $route, array $args = array())
    {
        return HttpHelper::niceUrlForRoute($request, $route, $args);
    }

    public static function tr($key, $args = array())
    {
        // TODO
    }

    /**
     * Get nice URL.
     *
     * @static
     * @param  string $url raw URL
     * @return string nice URL
     */
    public static function url($url)
    {
        return HttpHelper::niceUrl($url);
    }

    public static function session($name)
    {
        // TODO
    }
}
