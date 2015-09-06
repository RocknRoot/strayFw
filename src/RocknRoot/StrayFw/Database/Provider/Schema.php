<?php

namespace RocknRoot\StrayFw\Database\Provider;

use RocknRoot\StrayFw\Config;
use RocknRoot\StrayFw\Database\Mapping;
use RocknRoot\StrayFw\Exception\FileNotReadable;
use RocknRoot\StrayFw\Exception\InvalidDirectory;

/**
 * Schema representation parent class for all providers.
 *
 * @abstract
 *
 * @author Nekith <nekith@errant-works.com>
 */
abstract class Schema
{
    /**
     * Mapping name.
     *
     * @var string
     */
    protected $mapping;

    /**
     * Schema definition.
     *
     * @var array
     */
    protected $definition;

    /**
     * Construct a new schema representation class.
     *
     * @throws InvalidDirectory if directory $dir can't be indentified
     * @throws InvalidDirectory if directory $modelsDir can't be indentified
     * @throws InvalidDirectory if directory $modelsBaseDir can't be indentified
     * @throws InvalidDirectory if directory Base in $modelsBaseDir can't be indentified
     * @throws FileNotReadable  if file $configFile is not readable
     * @param  string           $mapping mapping name
     */
    public function __construct($mapping)
    {
        $this->mapping = $mapping;
        $data = Mapping::get($mapping);
        $dir = rtrim($data['dir'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $file = ltrim($data['config']['schema'], DIRECTORY_SEPARATOR);
        $modelsDir = null;
        if ($data['config']['models']['path'][0] == DIRECTORY_SEPARATOR) {
            $modelsDir = rtrim($data['config']['models']['path'], DIRECTORY_SEPARATOR);
        } else {
            $modelsDir = $dir . rtrim($data['config']['models']['path'], DIRECTORY_SEPARATOR);
        }
        if (is_dir($dir) === false) {
            throw new InvalidDirectory('directory "' . $dir . '" can\'t be identified');
        }
        if (is_readable($dir . $file) === false) {
            throw new FileNotReadable('file "' . $dir . $file . '" isn\'t readable');
        }
        if (is_dir($modelsDir) === false) {
            throw new InvalidDirectory('directory "' . $modelsDir . '" can\'t be identified');
        }
        if (is_dir($modelsDir . DIRECTORY_SEPARATOR . 'Base') === false) {
            throw new InvalidDirectory('directory "' . $modelsDir  . DIRECTORY_SEPARATOR . 'Base" can\'t be identified');
        }
    }

    /**
     * Build data structures.
     *
     * @abstract
     */
    abstract public function build();

    /**
     * Generate base models.
     *
     * @abstract
     */
    abstract public function generateModels();

    /**
     * Get the schema definition, from schema configuration file.
     *
     * @return array schema definition
     */
    public function getDefinition()
    {
        if ($this->definition == null) {
            $data = Mapping::get($this->mapping);
            $this->definition = Config::get(rtrim($data['dir'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ltrim($data['config']['schema'], DIRECTORY_SEPARATOR));
        }

        return $this->definition;
    }

    /**
     * Get mapping name this schema is embedded in.
     *
     * @return string mapping name
     */
    public function getMapping()
    {
        return $this->mapping;
    }

    /**
     * Get the schema instance of specified mapping.
     *
     * @param  string $mapping mapping name
     * @return Schema schema instance
     */
    public static function getSchema($mapping)
    {
        $data = Mapping::get($mapping);
        $namespace = substr(__NAMESPACE__, 0, strripos(__NAMESPACE__, '\\'));
        $class = $namespace . '\\' . rtrim(ucfirst($data['config']['provider']), '\\') . '\\Schema';

        return new $class($mapping);
    }
}
