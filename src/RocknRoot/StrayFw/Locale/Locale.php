<?php

namespace RocknRoot\StrayFw\Locale;

use RocknRoot\StrayFw\Config;
use RocknRoot\StrayFw\Exception\BadUse;
use RocknRoot\StrayFw\Exception\InvalidDirectory;
use RocknRoot\StrayFw\Http\Helper as HttpHelper;
use RocknRoot\StrayFw\Http\RawRequest;
use RocknRoot\StrayFw\Http\Session;
use RocknRoot\StrayFw\Logger;

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
     */
    private static bool $isInit = false;

    /**
     * Current language. Should be IETF language tag compliant.
     *
     * @see http://en.wikipedia.org/wiki/IETF_language_tag
     *
     * @static
     */
    protected static ?string $currentLanguage = null;

    /**
     * Loaded translations.
     *
     * @static
     * @var mixed[]
     */
    protected static array $translations = array();

    /**
     * Initialize properties, detect current language and load translations.
     *
     * @static
     * @param  RawRequest $request base raw request if applied
     * @throws BadUse     if locale.default isn\'t defined in settings
     */
    public static function init(RawRequest $request = null): void
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
     * @param  string           $baseDir    application directory path
     * @param  string           $localesDir translations directory path
     * @param  string           $prefix     prefix for all translations from this directory
     * @throws BadUse           if called while language is unset
     * @throws InvalidDirectory if directory can't be identified
     */
    public static function registerTranslations(string $baseDir, string $localesDir, string $prefix = null): void
    {
        if (!self::$currentLanguage) {
            throw new BadUse('Locale\Locale: language must be set before calling registerTranslation');
        }
        if (self::$isInit === true) {
            $dir = $baseDir . DIRECTORY_SEPARATOR . $localesDir;
            if (\is_dir($dir) === false) {
                throw new InvalidDirectory('directory "' . $dir . '" can\'t be identified');
            }
            $language = self::$currentLanguage ?? '_';
            if (($pos = \strpos($language, '-')) !== false) {
                $pos = (int) $pos;
                $language = \substr($language, 0, $pos);
            }
            if (($pos = \strpos($language, '_')) !== false) {
                $pos = (int) $pos;
                $language = \substr($language, 0, $pos);
            }
            if (\is_readable($dir . DIRECTORY_SEPARATOR . $language . '.yml') === true) {
                $newOnes = Config::get($dir . DIRECTORY_SEPARATOR . $language . '.yml');
                if (\is_array($newOnes) === true) {
                    if ($prefix != null) {
                        $newOnes = array($prefix => $newOnes);
                    }
                    self::$translations = \array_merge(self::$translations, $newOnes);
                }
            } else {
                Logger::get()->notice('can\'t find language "' . $language . '" in directory "' . $dir . '"');
            }
        }
    }

    /**
     * Get a translation from loaded files.
     *
     * @static
     * @param  string   $key  translation key
     * @param  string[] $args translation arguments values
     * @throws BadUse   if locale isn't initialized
     */
    public static function translate(string $key, array $args = []): string
    {
        if (self::$isInit === false) {
            throw new BadUse('locale doesn\'t seem to have been initialized');
        }
        $oldKey = $key;
        $section = self::$translations;
        while (isset($section[$key]) === false && ($pos = \strpos($key, '.')) !== false) {
            $subSection = \substr($key, 0, $pos);
            if (isset($section[$subSection]) === false) {
                break;
            }
            $section = $section[$subSection];
            $key = \substr($key, $pos + 1);
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
    public static function setCurrentLanguage(string $language): void
    {
        self::$currentLanguage = $language;
        Session::set('_stray_language', self::$currentLanguage);
        \setlocale(LC_ALL, $language);
    }

    /**
     * Get current language. Should be IETF language tag compliant.
     *
     * @static
     */
    public static function getCurrentLanguage(): ?string
    {
        return self::$currentLanguage;
    }
}
