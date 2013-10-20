<?php

namespace ErrantWorks\StrayFw\Render;

use ErrantWorks\StrayFw\Http\Helper as HttpHelper;
use ErrantWorks\StrayFw\Http\Session;
use ErrantWorks\StrayFw\Locale\Locale;

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

    /**
     * Get a translation from loaded files.
     *
     * @static
     * @param  string $key  translation key
     * @param  array  $args translation arguments values
     * @return string translated content
     */
    public static function tr($key, $args = array())
    {
        return Locale::translate($key, $args);
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

    /**
     * Get a session variable value by its key.
     *
     * @static
     * @param  string $name key
     * @return mixed
     */
    public static function session($name)
    {
        return Session::get($name);
    }
}
