<?php

namespace ErrantWorks\StrayFw;

use ErrantWorks\StrayFw\Exception\BadUse;
use ErrantWorks\StrayFw\Exception\UnknownNamespace;

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
     * Namespace-path hash.
     *
     * @var string[]
     */
    protected static $namespaces;

    /**
     * Initialize properties and register autoloader static method.
     */
    public static function init()
    {
        $namespaces = array();
        spl_autoload_register(__CLASS__ . '::loadClass');
    }

    /**
     * Autoloader registered function.
     * Try to require a file according to the needed class.
     *
     * @param string $className needed class name
     */
    public static function loadClass($className)
    {
        $fileName = null;
        if (($namespacePos = strripos($className, '\\')) !== false) {
            $namespace = substr($className, 0, $namespacePos);
            $subNamespaces = null;
            while ($fileName === null && $namespace != null) {
                if (isset(self::$namespaces[$namespace]) === false) {
                    $subNamespacePos = strripos($namespace, '\\');
                    $subNamespaces .= substr($namespace, $subNamespacePos);
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
                . str_replace('\\', DIRECTORY_SEPARATOR, $subNamespaces);
            $className = substr($className, $namespacePos + 1);
        } else {
            $namespace = substr($className, 0, stripos($className, '_') + 1);
            if (isset(self::$namespaces[$namespace]) === true) {
                $fileName = self::$namespaces[$namespace];
                $className = substr($className, stripos($className, '_') + 1);
            }
        }
        $fileName .= DIRECTORY_SEPARATOR
            . str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
        require $fileName;
    }

    public static function registerApp($namespace, $path = null)
    {
    }

    /**
     * Add a namespace to the recognized ones.
     *
     * @param string $namespace new namespace
     * @param string $path custom files path if needed
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
     * Launch the application. Bootstrap need to be init beforehand.
     */
    public static function run()
    {
        if (isset(self::$namespaces) === false) {
            throw new BadUse('bootstrap doesn\'t seem to have been initialized');
        }
        if (count(self::$namespaces) == 0) {
            throw new BadUse('no namespace has been registered');
        }
        if (STRAY_IS_CLI === true) {
        } else {
            if (STRAY_ENV === 'development') {
                Debug\ErrorPage::init();
            }
        }
    }
}
