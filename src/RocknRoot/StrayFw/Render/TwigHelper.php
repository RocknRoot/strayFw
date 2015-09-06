<?php

namespace RocknRoot\StrayFw\Render;

use RocknRoot\StrayFw\Http\Helper as HttpHelper;
use RocknRoot\StrayFw\Http\Request;
use RocknRoot\StrayFw\Http\Session;
use RocknRoot\StrayFw\Locale\Date;
use RocknRoot\StrayFw\Locale\Locale;

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
     * Display framework HTML body content.
     *
     * @static
     * @return string
     */
    public static function fwBody()
    {
        if (STRAY_ENV === 'development') {
            echo \RocknRoot\StrayFw\Debug\Bar::getBody();
        }
    }

    /**
     * Display framework HTML head content.
     *
     * @static
     * @return string
     */
    public static function fwHead()
    {
        if (STRAY_ENV === 'development') {
            echo '<script type="text/javascript" src="//code.jquery.com/jquery-1.11.3.min.js"></script>' . PHP_EOL;
            echo \RocknRoot\StrayFw\Debug\Bar::getHead();
        }
    }

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
     * Get a localized date from a time stamp.
     *
     * @static
     * @param  int|string $time   time stamp or 'now'
     * @param  string     $format date format
     * @return string     localized formatted date
     */
    public static function localizedDate($time, $format)
    {
        if ($time === 'now') {
            $time = time();
        }
        $date = new Date();
        $date->setPattern($format);

        return $date->format($time);
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
