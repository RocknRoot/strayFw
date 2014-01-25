<?php

namespace ErrantWorks\StrayFw\Database;

use ErrantWorks\StrayFw\Config;
use ErrantWorks\StrayFw\Logger;
use ErrantWorks\StrayFw\Exception\BadUse;
use ErrantWorks\StrayFw\Exception\FileNotReadable;
use ErrantWorks\StrayFw\Exception\InvalidDirectory;
use ErrantWorks\StrayFw\Exception\MappingNotFound;

/**
 * Mapping representation class.
 *
 * @author Nekith <nekith@errant-works.com>
 */
class Mapping
{
    /**
     * Registered mappings.
     *
     * @static
     * @var array[]
     */
    protected static $mappings = array();

    /**
     * Register a new mapping.
     *
     * @static
     * @throws InvalidDirectory if directory $dir can't be indentified
     * @throws FileNotReadable  if file $configFile is not readable
     * @param  string           $dir        application root directory
     * @param  string           $configFile mapping configuration file
     */
    public static function registerMapping($dir, $configFile)
    {
        if (is_dir($dir) === false) {
            throw new InvalidDirectory('directory "' . $dir . '" can\'t be identified');
        }
        if (is_readable($dir . DIRECTORY_SEPARATOR . $configFile) === false) {
            throw new FileNotReadable('file "' . $dir . DIRECTORY_SEPARATOR . $configFile . '" isn\'t readable');
        }
        $config = Config::get(rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ltrim($configFile, DIRECTORY_SEPARATOR));
        self::validateConfig($config);
        if (isset(self::$mappings[$config['name']]) === false) {
            self::$mappings[$config['name']] =  array(
                'dir' => $dir,
                'file' => $configFile,
                'config' => $config
            );
        } else {
            Logger::get()->warning('mapping with name "' . $config['name'] . '" was already set');
        }
    }

    /**
     * Get all mappings.
     *
     * @return array[] mappings
     */
    public static function getMappings()
    {
        return self::$mappings;
    }

    /**
     * Get the mapping data.
     *
     * @throws MappingNotFound if there's no registered mapping for specified database
     * @param  string          $name mapping name
     * @return array           mapping data
     */
    public static function get($name)
    {
        if (isset(self::$mappings[$name]) === false) {
            throw new MappingNotFound('there\'s no registered mapping with name "' . $name . '"');
        }

        return self::$mappings[$name];
    }

    /**
     * Check the validation of mapping configuration.
     *
     * @throws BadUse if there's no name in mapping configuration
     * @throws BadUse if there's no schema in mapping configuration
     * @throws BadUse if there's no provider in mapping configuration
     * @throws BadUse if there's no models in mapping configuration
     * @throws BadUse if there's no models.path in mapping configuration
     * @throws BadUse if there's no models.namespace in mapping configuration
     * @param  array  $config mapping configuration
     */
    private static function validateConfig(array $config)
    {
        if (isset($config['name']) === false) {
            throw new BadUse('there\'s no name in mapping configuration');
        }
        if (isset($config['schema']) === false) {
            throw new BadUse('there\'s no schema in mapping configuration');
        }
        if (isset($config['provider']) === false) {
            throw new BadUse('there\'s no provider in mapping configuration');
        }
        if (isset($config['models']) === false) {
            throw new BadUse('there\'s no models in mapping configuration');
        }
        if (isset($config['models']['path']) === false) {
            throw new BadUse('there\'s no models.path in mapping configuration');
        }
        if (isset($config['models']['namespace']) === false) {
            throw new BadUse('there\'s no models.namespace in mapping configuration');
        }
    }
}
