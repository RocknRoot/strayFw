<?php

namespace RocknRoot\StrayFw;

use RocknRoot\StrayFw\Console\Console;
use RocknRoot\StrayFw\Exception\BadUse;
use RocknRoot\StrayFw\Exception\UnknownNamespace;
use RocknRoot\StrayFw\Http\Http;
use Symfony\Component\ErrorHandler\Debug;

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
     */
    private static bool $isInit = false;

    /**
     * Namespace-path hash.
     *
     * @static
     * @var array<string, string>
     */
    protected static array $namespaces = [];

    /**
     * Registered applications.
     *
     * @static
     * @var string[]
     */
    protected static array $applications = [];

    /**
     * Initialize properties and register autoloader static method.
     *
     * @static
     */
    public static function init(): void
    {
        if (self::$isInit === false) {
            \spl_autoload_register(function (string $name): void {
                self::loadClass($name);
            });
            self::$isInit = true;
            Console::init();
            Console::prefix('\\RocknRoot\\StrayFw\\Console');
            Console::route('help', 'help', 'this screen', 'Controller.help');
            Console::prefix('\\RocknRoot\\StrayFw\\Database');
            Console::route('db/list', 'db/list', 'list registered mappings', 'Console.mappings');
            Console::route('db/build', 'db/build mapping_name', 'build data structures', 'Console.build');
            Console::route('db/generate', 'db/generate mapping_name', 'generate base models', 'Console.generate');
            Console::route('db/migration/create', 'db/migration/create mapping_name migration_name', 'create a new migration', 'Migration.create');
            Console::route('db/migration/generate', 'db/migration/generate mapping_name migration_name', 'generate migration code', 'Migration.generate');
            Console::route('db/migration/migrate', 'db/migration/migrate mapping_name', 'migrate', 'Migration.migrate');
            Console::route('db/migration/rollback', 'db/migration/rollback mapping_name', 'rollback last migration', 'Migration.rollback');
            Console::prefix('\\RocknRoot\\StrayFw\\Http');
            Console::route('http/routing/list', 'http/routing/list', 'list registered routes', 'Console.routes');
            Http::init();
            if (\defined('STRAY_IS_HTTP') === true && \constant('STRAY_IS_HTTP') === true && \constant('STRAY_ENV') === 'development') {
                Debug::enable();
            }
        }
    }

    /**
     * Autoloader registered function.
     * Try to require a file according to the needed class.
     *
     * @static
     * @param  string           $className needed class name
     * @throws BadUse           if bootstrap isn't initialized
     * @throws UnknownNamespace if needed namespace can't be found
     */
    public static function loadClass(string $className): void
    {
        if (self::$isInit === false) {
            throw new BadUse('bootstrap doesn\'t seem to have been initialized');
        }
        $fileName = null;
        if (($namespacePos = \strripos($className, '\\')) !== false) {
            $namespacePos = (int) $namespacePos; // re: https://github.com/phpstan/phpstan/issues/647
            $namespace = \substr($className, 0, $namespacePos);
            $subNamespaces = array();
            while ($fileName === null && $namespace != null) {
                if (isset(self::$namespaces[$namespace]) === false) {
                    $subNamespacePos = \strripos($namespace, '\\');
                    $subNamespacePos = (int) $subNamespacePos; // re: https://github.com/phpstan/phpstan/issues/647
                    $subNamespaces[] = \substr($namespace, $subNamespacePos);
                    $namespace = \substr($namespace, 0, $subNamespacePos);
                } else {
                    $fileName = self::$namespaces[$namespace];
                }
            }
            if ($fileName === null) {
                throw new UnknownNamespace('can\'t find namespace "' . \substr($className, 0, $namespacePos) . '"');
            }
            $fileName = self::$namespaces[$namespace] . \str_replace('\\', DIRECTORY_SEPARATOR, \implode('', \array_reverse($subNamespaces)));
            $className = \substr($className, $namespacePos + 1);
        }
        if ($fileName != null) {
            $fileName .= DIRECTORY_SEPARATOR . \str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
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
    public static function registerApp(string $namespace, string $path = null): void
    {
        $namespace = \rtrim($namespace, '\\');
        if ($path == null) {
            $path = \constant('STRAY_PATH_APPS') . \str_replace(
                '_',
                DIRECTORY_SEPARATOR,
                \str_replace('\\', DIRECTORY_SEPARATOR, $namespace)
            );
        }
        self::$namespaces[$namespace] = $path;
        self::$applications[] = $namespace;
    }

    /**
     * Launch the logic stuff. Bootstrap need to be initialized beforehand.
     *
     * @throws BadUse if bootstrap isn't initialized
     * @throws BadUse if no application is registered
     * @throws BadUse if not CLI_IS_CLI nor STRAY_IS_HTTP
     * @static
     */
    public static function run(): void
    {
        if (self::$isInit === false) {
            throw new BadUse('bootstrap doesn\'t seem to have been initialized');
        }
        foreach (self::$namespaces as $name => $path) {
            if (\is_readable($path . DIRECTORY_SEPARATOR . 'init.php') === true) {
                require $path . DIRECTORY_SEPARATOR . 'init.php';
            } elseif (\stripos($path, 'vendor') === false || \stripos($path, 'vendor') == \strlen($path) - \strlen('vendor')) {
                Logger::get()->error('namespace "' . $name . '" doesn\'t have an init.php');
            }
        }
        if (\defined('STRAY_IS_CLI') === true && \constant('STRAY_IS_CLI') === true) {
            Console::run();
        } elseif (\defined('STRAY_IS_HTTP') === true && \constant('STRAY_IS_HTTP') === true) {
            if (\count(self::$applications) == 0) {
                throw new BadUse('no application has been registered');
            }
            Http::run();
        } else {
            throw new BadUse('unknown mode, not CLI_IS_CLI nor STRAY_IS_HTTP');
        }
    }
}
