<?php

namespace RocknRoot\StrayFw\Render;

use RocknRoot\StrayFw\Http\Helper as HttpHelper;
use RocknRoot\StrayFw\Http\Session;
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
     * Get a translation from loaded files.
     *
     * @static
     * @param  string $key  translation key
     * @param  array  $args translation arguments values
     * @return string translated content
     */
    public static function tr(string $key, array $args = array()) : string
    {
        return Locale::translate($key, $args);
    }

    /**
     * Get full tag for current language.
     *
     * @static
     * @return string tag
     */
    public static function langFull() : string
    {
        return Locale::getCurrentLanguage();
    }

    /**
     * Get primary tag for current language.
     *
     * @static
     * @return string primary tag
     */
    public static function langPrimary() : string
    {
        $lang = Locale::getCurrentLanguage();
        if (($pos = strpos($lang, '-')) !== false) {
            $lang = substr($lang, 0, $pos);
        }
        if (($pos = strpos($lang, '_')) !== false) {
            $lang = substr($lang, 0, $pos);
        }
        return $lang;
    }

    /**
     * Get nice URL.
     *
     * @static
     * @param  string $url raw URL
     * @return string nice URL
     */
    public static function url(string $url) : string
    {
        return HttpHelper::niceUrl($url);
    }

    /**
     * Get a localized date from a time stamp.
     *
     * @static
     * @param  int|string $time       time stamp or 'now'
     * @param  int        $dateFormat date format
     * @param  int        $timeFormat time format
     * @return string     localized formatted date
     */
    public static function localizedDate($time, int $dateFormat, int $timeFormat) : string
    {
        if ($time === 'now') {
            $time = time();
        }
        $date = \IntlDateFormatter::create(Locale::getCurrentLanguage(), $dateFormat, $timeFormat);

        return $date->format($time);
    }

    /**
     * Get a session variable value by its key.
     *
     * @static
     * @param  string $name key
     * @return mixed
     */
    public static function session(string $name)
    {
        return Session::get($name);
    }
}
