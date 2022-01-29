<?php

namespace RocknRoot\StrayFw\Database;

use RocknRoot\StrayFw\Exception\BadUse;
use RocknRoot\StrayFw\Exception\MappingNotFound;
use RocknRoot\StrayFw\Logger;

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
    protected static array $mappings = array();

    /**
     * Register a new mapping.
     *
     * @static
     * @param mixed[] $config mapping configuration
     */
    public static function registerMapping(array $config): void
    {
        self::validateConfig($config);
        if (isset(self::$mappings[$config['name']]) === false) {
            self::$mappings[$config['name']] = array(
                'config' => $config
            );
            Database::registerDatabase($config['database']);
        } else {
            Logger::get()->warning('mapping with name "' . $config['name'] . '" was already set');
        }
    }

    /**
     * Get all mappings.
     *
     * @return array[] mappings
     */
    public static function getMappings(): array
    {
        return self::$mappings;
    }

    /**
     * Get the mapping data.
     *
     * @param  string                 $name mapping name
     * @throws MappingNotFound        if there's no registered mapping for specified database
     * @return array<string,   mixed> mapping data
     */
    public static function get(string $name): array
    {
        if (isset(self::$mappings[$name]) === false) {
            throw new MappingNotFound('there\'s no registered mapping with name "' . $name . '"');
        }
        return self::$mappings[$name];
    }

    /**
     * Check the validation of mapping configuration.
     *
     * @param  mixed[] $config mapping configuration
     * @throws BadUse  if there's no name in mapping configuration
     * @throws BadUse  if there's no schema in mapping configuration
     * @throws BadUse  if there's no provider in mapping configuration
     * @throws BadUse  if there's no models in mapping configuration
     * @throws BadUse  if there's no models.path in mapping configuration
     * @throws BadUse  if there's no models.namespace in mapping configuration
     */
    private static function validateConfig(array $config): void
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
