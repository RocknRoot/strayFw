<?php

namespace ErrantWorks\StrayFw\Locale;

use ErrantWorks\StrayFw\Config;
use ErrantWorks\StrayFw\Exception\BadUse;
use ErrantWorks\StrayFw\Http\Helper as HttpHelper;
use ErrantWorks\StrayFw\Http\RawRequest;
use ErrantWorks\StrayFw\Http\Session;
use ErrantWorks\StrayFw\Logger;

/**
 * Internationalization and localization base class.
 *
 * @abstract
 *
 * @author Nekith <nekith@errant-works.com>
 */
abstract class Locale
{
    /**
     * True if class has already been initialized.
     *
     * @static
     * @var bool
     */
    private static $isInit = false;

    /**
     * Current language. Should be IETF language tag compliant.
     *
     * @see http://en.wikipedia.org/wiki/IETF_language_tag
     *
     * @static
     * @var string
     */
    protected static $currentLanguage = null;

    /**
     * Loaded translations.
     *
     * @static
     * @var mixed[]
     */
    protected static $translations = array();

    /**
     * Initialize properties, detect current language and load translations.
     *
     * @static
     * @throws BadUse     if locale.default isn\'t defined in settings
     * @param  RawRequest $request base raw request if applied
     */
    public static function init(RawRequest $request = null)
    {
        if (self::$isInit === false) {
            self::$translations = array();
            $settings = Config::getSettings();
            if (isset($settings['locale']) === false || isset($settings['locale']['default']) === false) {
                throw new BadUse('locale.default isn\'t defined in settings');
            }
            self::$currentLanguage = $settings['locale']['default'];
            if ($request != null) {
                if (Session::has('_stray_language') === true) {
                    self::$currentLanguage = Session::get('_stray_language');
                } else {
                    $domain = HttpHelper::extractDomain($request);
                    if (isset($settings['locale']['hosts']) === true && isset($settings['locale']['hosts'][$domain]) === true) {
                        self::$currentLanguage = $settings['locale']['hosts'][$domain];
                    }
                    Session::set('_stray_language', self::$currentLanguage);
                }
            }
            self::$isInit = true;
        }
    }

    /**
     * Load translations from directory according to current language.
     *
     * @static
     * @throws BadUse           if locale isn't initialized
     * @throws InvalidDirectory if directory can't be identified
     * @param  string           $baseDir    application directory path
     * @param  string           $localesDir translations directory path
     * @param  string           $prefix     prefix for all translations from this directory
     */
    public static function registerTranslations($baseDir, $localesDir, $prefix = null)
    {
        $dir = $baseDir . DIRECTORY_SEPARATOR . $localesDir;
        if (self::$isInit === false) {
            throw new BadUse('locale doesn\'t seem to have been initialized');
        }
        if (is_dir($dir) === false) {
            throw new InvalidDirectory('directory "' . $dir . '" can\'t be identified');
        }
        $language = self::$currentLanguage;
        if (($pos = strpos($language, '_')) !== false) {
            $language = substr($language, 0, $pos);
        }
        if (file_exists($dir . DIRECTORY_SEPARATOR . $language . '.yml') === true) {
            $newOnes = Config::get($dir . DIRECTORY_SEPARATOR . $language . '.yml');
            if (is_array($newOnes) === true) {
                if ($prefix != null) {
                    $newOnes = array($prefix => $newOnes);
                }
                self::$translations = array_merge(self::$translations, $newOnes);
            }
        } else {
            Logger::get()->notice('can\'t find language "' . $language . '" in directory "' . $dir . '"');
        }
    }

    /**
     * Get a translation from loaded files.
     *
     * @static
     * @throws BadUse if locale isn't initialized
     */
    public static function translate($key, array $args = array())
    {
        if (self::$isInit === false) {
            throw new BadUse('locale doesn\'t seem to have been initialized');
        }
        $oldKey = $key;
        $section = self::$translations;
        while (isset($section[$key]) === false && ($pos = strpos($key, '.')) !== false) {
            $subSection = substr($key, 0, $pos);
            if (isset($section[$subSection]) === false) {
                break;
            }
            $section = $section[$subSection];
            $key = substr($key, $pos + 1);
        }
        if (isset($section[$key]) === false) {
            Logger::get()->error('can\'t find translation for key "' . $oldKey . '"');

            return '(null)';
        }

        return $section[$key];
    }

    /**
     * Set current language. Should be IETF language tag compliant.
     *
     * @static
     * @param string $language
     */
    public static function setCurrentLanguage($language)
    {
        self::$currentLanguage = $language;
        Session::set('_stray_language', self::$currentLanguage);
    }

    /**
     * Get current language. Should be IETF language tag compliant.
     *
     * @static
     * @return string
     */
    public static function getCurrentLanguage()
    {
        return self::$currentLanguage;
    }
}
