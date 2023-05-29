<?php

namespace RocknRoot\StrayFw\Database\Provider;

use RocknRoot\StrayFw\Config;
use RocknRoot\StrayFw\Database\Mapping;
use RocknRoot\StrayFw\Exception\AppException;
use RocknRoot\StrayFw\Exception\BadUse;
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
     * @var array<string, mixed>
     */
    protected ?array $definition = null;

    /**
     * Construct a new schema representation class.
     *
     * @param  string           $mapping mapping name
     * @throws BadUse           if mapping is incorrectly defined
     * @throws InvalidDirectory if directory $modelsDir can't be indentified
     * @throws InvalidDirectory if directory Base in $modelsDir can't be indentified
     * @throws FileNotReadable  if schema file is not readable
     */
    public function __construct(string $mapping)
    {
        $this->mapping = $mapping;
        $data = Mapping::get($mapping);
        if (!isset($data['config']['schema'])) {
            throw new BadUse('mapping "' . $mapping . '" does not have a "schema" entry');
        }
        if (!isset($data['config']['models'])) {
            throw new BadUse('mapping "' . $mapping . '" does not have a "models" entry');
        }
        if (!isset($data['config']['models']['path'])) {
            throw new BadUse('mapping "' . $mapping . '" does not have a "models/path" entry');
        }
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
     * @return array<string, mixed> schema definition
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
     * @throws BadUse if specified provider class does not inherit from Provider\Schema
     * @return self   schema instance
     */
    public static function getSchema(string $mapping): self
    {
        $data = Mapping::get($mapping);
        $class = \rtrim(\ucfirst($data['config']['provider']), '\\') . '\\Schema';
        $parents = class_parents($class);
        if (!$parents) {
            throw new AppException('cannot get parents of class "' . $class . '"');
        }
        $parents = array_keys($parents);
        if (!in_array(self::class, $parents)) {
            throw new BadUse('specified provider class "' . $class . '" does not inherit from Provider\Schema');
        }
        return new $class($mapping); // @phpstan-ignore-line @TODO cannot infer that this is a child of Schema
    }
}
