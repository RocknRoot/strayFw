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
     */
    protected string $mapping;

    /**
     * Schema definition.
     *
     * @var array[]
     */
    protected ?array $definition = null;

    /**
     * Construct a new schema representation class.
     *
     * @param  string           $mapping mapping name
     * @throws InvalidDirectory if directory $modelsDir can't be indentified
     * @throws InvalidDirectory if directory Base in $modelsDir can't be indentified
     * @throws FileNotReadable  if schema file is not readable
     */
    public function __construct(string $mapping)
    {
        $this->mapping = $mapping;
        $data = Mapping::get($mapping);
        $file = $data['config']['schema'];
        $modelsDir = $data['config']['models']['path'];
        if (\is_readable($file) === false) {
            throw new FileNotReadable('file "' . $file . '" isn\'t readable');
        }
        if (\is_dir($modelsDir) === false) {
            throw new InvalidDirectory('directory "' . $modelsDir . '" can\'t be identified');
        }
        if (\is_dir($modelsDir . DIRECTORY_SEPARATOR . 'Base') === false) {
            throw new InvalidDirectory('directory "' . $modelsDir . DIRECTORY_SEPARATOR . 'Base" can\'t be identified');
        }
    }

    /**
     * Build data structures.
     *
     * @abstract
     */
    abstract public function build(): void;

    /**
     * Generate base models.
     *
     * @abstract
     */
    abstract public function generateModels(): void;

    /**
     * Get the schema definition, from schema configuration file.
     *
     * @return array<string, array> schema definition
     */
    public function getDefinition(): array
    {
        if ($this->definition == null) {
            $data = Mapping::get($this->mapping);
            $this->definition = Config::get($data['config']['schema']);
        }

        return $this->definition;
    }

    /**
     * Get mapping name this schema is embedded in.
     *
     * @return string mapping name
     */
    public function getMapping(): string
    {
        return $this->mapping;
    }

    /**
     * Get the schema instance of specified mapping.
     *
     * @param  string $mapping mapping name
     * @return Schema schema instance
     */
    public static function getSchema(string $mapping): Schema
    {
        $data = Mapping::get($mapping);
        $class = \rtrim(\ucfirst($data['config']['provider']), '\\') . '\\Schema';

        return new $class($mapping);
    }
}
