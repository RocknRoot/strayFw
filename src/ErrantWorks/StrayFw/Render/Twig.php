<?php

namespace ErrantWorks\StrayFw\Render;

use ErrantWorks\StrayFw\Config;
use ErrantWorks\StrayFw\Exception\BadUse;
use ErrantWorks\StrayFw\Exception\InvalidDirectory;

/**
 * Wrapper and configuration class for Twig.
 *
 * @abstract
 *
 * @author Nekith <nekith@errant-works.com>
 */
abstract class Twig
{
    /**
     * Existing Twig environments.
     *
     * @static
     * @var Twig_Environment[]
     */
    protected static $environments = array();

    /**
     * Registered extensions.
     *
     * @static
     * @var string[]
     */
    protected static $extensions = array();

    /**
     * Registered functions.
     *
     * @static
     * @var string[]
     */
    protected static $functions = array();

    /**
     * Get environment for specified templates directory.
     *
     * @static
     * @throws InvalidDirectory if directory can't be identified
     * @throws BadUse           if tmp path hasn't been defined
     * @throws BadUse           if tmp directory isn't writable
     * @param  string           $dir template directory
     * @return Twig_Environment corresponding environment
     */
    public static function getEnv($dir)
    {
        if (isset(self::$environments[$dir]) === false) {
            $dir = rtrim($dir, '/') . '/';
            if (is_dir($dir) === false) {
                throw new InvalidDirectory('invalid templates directory "' . $dir . '"');
            }
            $settings = Config::getSettings();
            if (empty($settings['tmp']) === true) {
                throw new BadUse('tmp directory hasn\'t been defined in installation settings');
            }
            $tmp = rtrim($settings['tmp'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            if ($tmp[0] != DIRECTORY_SEPARATOR) {
                $tmp = STRAY_PATH_ROOT . $tmp;
            }
            if (is_dir($tmp . 'twig_compil/') == false) {
                if (mkdir($tmp . 'twig_compil') === false) {
                    throw new BadUse('tmp directory doesn\'t seem to be writable');
                }
            }
            $loader = new \Twig_Loader_Filesystem($dir);
            $env = new \Twig_Environment($loader, array(
                'cache' => $tmp . 'twig_compil',
                'debug' => (STRAY_ENV === 'development')
            ));
            if (STRAY_ENV === 'development') {
                $env = new \DebugBar\Bridge\Twig\TraceableTwigEnvironment($env);
            }
            self::$environments[$dir] = $env;
            if (STRAY_ENV === 'development') {
                self::$environments[$dir]->addExtension(new \Twig_Extension_Debug());
            }
            self::$environments[$dir]->addFunction('fwBody', new \Twig_Function_Function('\\ErrantWorks\\StrayFw\\Render\\TwigHelper::fwBody'));
            self::$environments[$dir]->addFunction('fwHead', new \Twig_Function_Function('\\ErrantWorks\\StrayFw\\Render\\TwigHelper::fwHead'));
            self::$environments[$dir]->addFunction('route', new \Twig_Function_Function('\\ErrantWorks\\StrayFw\\Render\\TwigHelper::route'));
            self::$environments[$dir]->addFunction('tr', new \Twig_Function_Function('\\ErrantWorks\\StrayFw\\Render\\TwigHelper::tr'));
            self::$environments[$dir]->addFunction('url', new \Twig_Function_Function('\\ErrantWorks\\StrayFw\\Render\\TwigHelper::url'));
            self::$environments[$dir]->addFunction('session', new \Twig_Function_Function('\\ErrantWorks\\StrayFw\\Render\\TwigHelper::session'));
            foreach (self::$extensions as $ext) {
                self::$environments[$dir]->addExtension(new $ext);
            }
            foreach (self::$functions as $label => $name) {
                self::$environments[$dir]->addFunction($label, new \Twig_Function_Function($name));
            }
        }

        return self::$environments[$dir];
    }

    /**
     * Add an extension to Twig environments.
     *
     * @static
     * @param string $className extension class name
     */
    public static function addExtension($className)
    {
        if (array_search($className, self::$extensions) === false) {
            self::$extensions[] = $className;
            foreach (self::$environments as $env) {
                $env->addExtension(new $className());
            }
        }
    }

    /**
     * Add a function to Twig environments.
     *
     * @static
     * @param string $label       function name in Twig templates
     * @param string $functioName function name
     */
    public static function addFunction($label, $functionName)
    {
        if (isset(self::$functions[$label]) === false) {
            self::$functions[$label] = $functionName;
            foreach (self::$environments as $env) {
                $env->addFunction($label, new \Twig_Function_Function($functionName));
            }
        }
    }
}
