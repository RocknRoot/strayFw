<?php

namespace ErrantWorks\StrayFw;

use ErrantWorks\StrayFw\Console\Console;
use ErrantWorks\StrayFw\Exception\BadUse;
use ErrantWorks\StrayFw\Exception\UnknownNamespace;
use ErrantWorks\StrayFw\Http\Http;
use ErrantWorks\StrayFw\Logger;

/**
 * First loaded framework class, taking care of autoloading
 * and registering apps and libs.
 *
 * @abstract
 *
 * @author Nekith <nekith@errant-works.com>
 */
abstract class Bootstrap
{
    /**
     * True if class has already been initialized.
     *
     * @static
     * @var bool
     */
    private static $isInit = false;

    /**
     * Namespace-path hash.
     *
     * @static
     * @var string[]
     */
    protected static $namespaces;

    /**
     * Registered applications.
     *
     * @static
     * @var string[]
     */
    protected static $applications;

    /**
     * Initialize properties and register autoloader static method.
     *
     * @static
     */
    public static function init()
    {
        if (self::$isInit === false) {
            self::$namespaces = array();
            self::$applications = array();
            spl_autoload_register(__CLASS__ . '::loadClass');
            self::$isInit = true;
            self::registerLib('Psr\\Log');
            self::registerLib('Symfony\\Component\\Yaml');
            self::registerLib('ErrantWorks\\StrayFw');
            if (STRAY_ENV === 'development') {
                Bootstrap::registerLib('DebugBar', STRAY_PATH_VENDOR . 'Maximebf/DebugBar');
                Bootstrap::registerLib('Whoops', STRAY_PATH_VENDOR . 'Filp/Whoops');
            }
            if (defined('STRAY_IS_CLI') === true && STRAY_IS_CLI === true) {
                Console::init();
                Console::registerRoutes(__DIR__ . DIRECTORY_SEPARATOR . 'Console', 'console.yml');
                Console::registerRoutes(__DIR__ . DIRECTORY_SEPARATOR . 'Database', 'console.yml');
            } elseif (defined('STRAY_IS_HTTP') === true && STRAY_IS_HTTP === true) {
                if (STRAY_ENV === 'development') {
                    Debug\Bar::init();
                    Debug\ErrorPage::init();
                }
                Http::init();
            }
        }
    }

    /**
     * Autoloader registered function.
     * Try to require a file according to the needed class.
     *
     * @static
     * @throws BadUse           if bootstrap isn't initialized
     * @throws UnknownNamespace if needed namespace can't be found
     * @param  string           $className needed class name
     */
    public static function loadClass($className)
    {
        if (self::$isInit === false) {
            throw new BadUse('bootstrap doesn\'t seem to have been initialized');
        }
        $fileName = null;
        if (($namespacePos = strripos($className, '\\')) !== false) {
            $namespace = substr($className, 0, $namespacePos);
            $subNamespaces = array();
            while ($fileName === null && $namespace != null) {
                if (isset(self::$namespaces[$namespace]) === false) {
                    $subNamespacePos = strripos($namespace, '\\');
                    $subNamespaces[] = substr($namespace, $subNamespacePos);
                    $namespace = substr($namespace, 0, $subNamespacePos);
                } else {
                    $fileName = self::$namespaces[$namespace];
                }
            }
            if ($fileName === null) {
                throw new UnknownNamespace('can\'t find namespace "'
                    . substr($className, 0, $namespacePos) . '"');
            }
            $fileName = self::$namespaces[$namespace]
                . str_replace('\\', DIRECTORY_SEPARATOR, implode(null, array_reverse($subNamespaces)));
            $className = substr($className, $namespacePos + 1);
        }
        if ($fileName != null) {
            $fileName .= DIRECTORY_SEPARATOR . str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
            require $fileName;
        }
    }

    /**
     * Add a namespace to the recognized ones.
     * Use this for files in the _apps_ directory.
     *
     * @static
     * @param string $namespace new namespace
     * @param string $path      custom files path if needed
     */
    public static function registerApp($namespace, $path = null)
    {
        $namespace = rtrim($namespace, '\\');
        if ($path == null) {
            $path = STRAY_PATH_APPS . str_replace('_', DIRECTORY_SEPARATOR,
                str_replace('\\', DIRECTORY_SEPARATOR, $namespace));
        }
        self::$namespaces[$namespace] = $path;
        self::$applications[] = $namespace;
    }

    /**
     * Add a namespace to the recognized ones.
     * Use this for files in the _vendor_ directory.
     *
     * @static
     * @param string $namespace new namespace
     * @param string $path      custom files path if needed
     */
    public static function registerLib($namespace, $path = null)
    {
        $namespace = rtrim($namespace, '\\');
        if ($path == null) {
            $path = STRAY_PATH_VENDOR . str_replace('_', DIRECTORY_SEPARATOR,
                str_replace('\\', DIRECTORY_SEPARATOR, $namespace));
        }
        self::$namespaces[$namespace] = $path;
    }

    /**
     * Launch the logic stuff. Bootstrap need to be initialized beforehand.
     *
     * @throws BadUse if bootstrap isn't initialized
     * @throws BadUse if no application is registered
     * @static
     */
    public static function run()
    {
        if (self::$isInit === false) {
            throw new BadUse('bootstrap doesn\'t seem to have been initialized');
        }
        foreach (self::$namespaces as $name => $path) {
            if (is_readable($path . DIRECTORY_SEPARATOR . 'init.php') === true) {
                require $path . DIRECTORY_SEPARATOR . 'init.php';
            } elseif (stripos($path, 'vendor') === false || stripos($path, 'vendor') == strlen($path) - strlen('vendor')) {
                Logger::get()->error('namespace "' . $name . '" doesn\'t have an init.php');
            }
        }
        if (defined('STRAY_IS_CLI') === true && STRAY_IS_CLI === true) {
            Console::run();
        } elseif (defined('STRAY_IS_HTTP') === true && STRAY_IS_HTTP === true) {
            if (count(self::$applications) == 0) {
                throw new BadUse('no application has been registered');
            }
            Http::run();
        }
    }
}
