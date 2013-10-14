<?php

namespace ErrantWorks\StrayFw;

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
     * @var string[]
     */
    protected static $namespaces;

    public static function init()
    {
        $namespaces = array();
        spl_autoload_register(__CLASS__ . '::loadClass');
    }

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
        }
        $fileName .= DIRECTORY_SEPARATOR
            . str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
        require $fileName;
    }

    public static function registerApp($namespace, $path = null)
    {
    }

    public static function registerLib($namespace, $path = null)
    {
        $namespace = rtrim($namespace, '\\');
        if ($path == null) {
            $path = STRAY_PATH_VENDOR . str_replace('_', DIRECTORY_SEPARATOR,
                str_replace('\\', DIRECTORY_SEPARATOR, $namespace));
        }
        self::$namespaces[$namespace] = $path;
    }

    public static function run()
    {
        if (STRAY_IS_CLI === true) {
        } else {
            if (STRAY_ENV === 'development') {
                $whoops = new \Whoops\Run();
                $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler());
                $whoops->register();
            }
        }
    }
}
