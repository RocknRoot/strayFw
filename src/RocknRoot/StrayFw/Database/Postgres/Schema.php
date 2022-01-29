<?php

namespace RocknRoot\StrayFw\Database\Postgres;

use RocknRoot\StrayFw\Database\Database as GlobalDatabase;
use RocknRoot\StrayFw\Database\Helper;
use RocknRoot\StrayFw\Database\Mapping;
use RocknRoot\StrayFw\Database\Provider\Schema as ProviderSchema;
use RocknRoot\StrayFw\Exception\DatabaseError;
use RocknRoot\StrayFw\Exception\FileNotWritable;
use RocknRoot\StrayFw\Exception\InvalidSchemaDefinition;

/**
 * Schema representation class for PostgreSQL ones.
 * User code shouldn't use this class directly.
 *
 * @author Nekith <nekith@errant-works.com>
 */
class Schema extends ProviderSchema
{
    /**
     * Build data structures.
     *
     * @throws DatabaseError           if a SQL query fails
     * @throws InvalidSchemaDefinition if a model has no field
     * @throws InvalidSchemaDefinition if an enum-typed field has no values defined
     */
    public function build(): void
    {
        $mapping = Mapping::get($this->mapping);
        $definition = $this->getDefinition();
        $database = GlobalDatabase::get($mapping['config']['database']);

        foreach ($definition as $modelName => $modelDefinition) {
            if (isset($modelDefinition['links']) === false) {
                continue;
            }
            $tableName = null;
            if (isset($modelDefinition['name']) === true) {
                $tableName = $modelDefinition['name'];
            } else {
                $tableName = Helper::codifyName($this->mapping) . '_' . Helper::codifyName($modelName);
            }
            $query = $database->getMasterLink()->query('SELECT COUNT(*) as count FROM pg_class WHERE relname = \'' . $tableName . '\'');
            $result = $query->fetch(\PDO::FETCH_ASSOC);
            if ($result['count'] != 0) {
                foreach ($modelDefinition['links'] as $keyName => $keyDefinition) {
                    $statement = Mutation\DeleteForeignKey::statement($database, $tableName, $keyName);
                    if ($statement->execute() == false) {
                        throw new DatabaseError('db/build : SQL query failed');
                    }
                }
            }
        }

        foreach ($definition as $modelName => $modelDefinition) {
            if (isset($modelDefinition['type']) === false || $modelDefinition['type'] === 'model') {
                $tableName = null;
                if (isset($modelDefinition['name']) === true) {
                    $tableName = $modelDefinition['name'];
                } else {
                    $tableName = Helper::codifyName($this->mapping) . '_' . Helper::codifyName($modelName);
                }
                $statement = Mutation\DeleteTable::statement($database, $tableName);
                if ($statement->execute() == false) {
                    throw new DatabaseError('db/build : SQL query failed');
                }
            }
        }
        foreach ($definition as $modelName => $modelDefinition) {
            if (isset($modelDefinition['type']) === true && $modelDefinition['type'] === 'enum') {
                $modelRealName = null;
                if (isset($modelDefinition['name']) === true) {
                    $modelRealName = $modelDefinition['name'];
                } else {
                    $modelRealName = Helper::codifyName($this->mapping) . '_' . Helper::codifyName($modelName);
                }
                $statement = Mutation\DeleteEnum::statement($database, $modelRealName);
                if ($statement->execute() == false) {
                    throw new DatabaseError('db/build : SQL query failed');
                }
            }
        }

        foreach ($definition as $modelName => $modelDefinition) {
            if (isset($modelDefinition['type']) === true && $modelDefinition['type'] === 'enum') {
                $this->buildEnum($modelName, $modelDefinition);
            }
        }
        foreach ($definition as $modelName => $modelDefinition) {
            if (isset($modelDefinition['type']) === false || $modelDefinition['type'] === 'model') {
                $this->buildModel($modelName, $modelDefinition);
            }
        }

        foreach ($definition as $modelName => $modelDefinition) {
            if ((isset($modelDefinition['type']) === false || $modelDefinition['type'] === 'model') && isset($modelDefinition['links']) === true) {
                foreach ($modelDefinition['links'] as $foreignName => $foreignDefinition) {
                    $tableName = null;
                    if (isset($modelDefinition['name']) === true) {
                        $tableName = $modelDefinition['name'];
                    } else {
                        $tableName = Helper::codifyName($this->mapping) . '_' . Helper::codifyName($modelName);
                    }
                    $foreignTableName = null;
                    if (isset($definition[$foreignDefinition['model']]['name']) === true) {
                        $foreignTableName = $definition[$foreignDefinition['model']]['name'];
                    } else {
                        $foreignTableName = Helper::codifyName($this->mapping) . '_' . Helper::codifyName($foreignDefinition['model']);
                    }
                    $statement = Mutation\AddForeignKey::statement($database, $definition, $modelName, $tableName, $foreignName, $foreignTableName);
                    if ($statement->execute() == false) {
                        throw new DatabaseError('db/build : SQL query failed');
                    }
                }
            }
        }
        Migration::ensureTable($mapping);
    }

    /**
     * Build an enum.
     *
     * @param  string                  $enumName       enum alias
     * @param  array<string, mixed>    $enumDefinition enum definition from a schema file
     * @throws DatabaseError           if a SQL query fails
     * @throws InvalidSchemaDefinition if an enum has no value
     */
    private function buildEnum(string $enumName, array $enumDefinition): void
    {
        $mapping = Mapping::get($this->mapping);
        $definition = $this->getDefinition();
        $database = GlobalDatabase::get($mapping['config']['database']);

        $enumRealName = null;
        if (isset($enumDefinition['name']) === true) {
            $enumRealName = $enumDefinition['name'];
        } else {
            $enumRealName = Helper::codifyName($this->mapping) . '_' . Helper::codifyName($enumName);
        }

        if (isset($enumDefinition['values']) === false) {
            throw new InvalidSchemaDefinition('enum "' . $enumName . '" has no value');
        }

        $values = array();
        foreach ($enumDefinition['values'] as $valueName => $valueAlias) {
            $valueRealName = null;
            if (\is_string($valueName) === true) {
                $valueRealName = $valueName;
            } else {
                $valueRealName = Helper::codifyName($enumName) . '_' . Helper::codifyName($valueAlias);
            }
            $values[] = $valueRealName;
        }

        $statement = Mutation\AddEnum::statement($database, $enumRealName, $values);
        if ($statement->execute() == false) {
            throw new DatabaseError('db/build : SQL query failed');
        }

        echo $enumName . ' - Done' . PHP_EOL;
    }

    /**
     * Build a model.
     *
     * @param  string                  $modelName       model alias
     * @param  array<string, mixed>    $modelDefinition model definition from a schema file
     * @throws DatabaseError           if a SQL query fails
     * @throws InvalidSchemaDefinition if a model has no field
     */
    private function buildModel(string $modelName, array $modelDefinition): void
    {
        $mapping = Mapping::get($this->mapping);
        $definition = $this->getDefinition();
        $database = GlobalDatabase::get($mapping['config']['database']);

        $tableName = null;
        if (isset($modelDefinition['name']) === true) {
            $tableName = $modelDefinition['name'];
        } else {
            $tableName = Helper::codifyName($this->mapping) . '_' . Helper::codifyName($modelName);
        }

        if (isset($modelDefinition['fields']) === false) {
            throw new InvalidSchemaDefinition('model "' . $modelName . '" has no field');
        }
        $statement = Mutation\AddTable::statement($database, $this->getDefinition(), $this->mapping, $tableName, $modelName);
        if ($statement->execute() == false) {
            throw new DatabaseError('db/build : SQL query failed');
        }

        if (isset($modelDefinition['indexes']) === true) {
            foreach ($modelDefinition['indexes'] as $indexName => $indexDefinition) {
                $statement = Mutation\AddIndex::statement($database, $modelName, $tableName, $modelDefinition, $indexName);
                if ($statement->execute() == false) {
                    throw new DatabaseError('db/build : SQL query failed');
                }
            }
        }

        if (isset($modelDefinition['uniques']) === true) {
            foreach ($modelDefinition['uniques'] as $uniqueName => $uniqueDefinition) {
                $statement = Mutation\AddUnique::statement($database, $modelName, $tableName, $modelDefinition, $uniqueName);
                if ($statement->execute() == false) {
                    throw new DatabaseError('db/build : SQL query failed');
                }
            }
        }

        echo $modelName . ' - Done' . PHP_EOL;
    }

    /**
     * Generate SQL entities' representing classes.
     *
     * @see generateEnum
     * @see generateModel
     */
    public function generateModels(): void
    {
        $definition = $this->getDefinition();
        foreach ($definition as $modelName => $modelDefinition) {
            $type = 'model';
            if (isset($modelDefinition['type']) === true && \in_array($modelDefinition['type'], [ 'enum', 'model' ]) === true) {
                $type = $modelDefinition['type'];
            }
            if ($type == 'enum') {
                $this->generateEnum($modelName, $modelDefinition);
            } else {
                $this->generateModel($modelName, $modelDefinition);
            }
        }
    }

    /**
     * Generate classes for a enum.
     *
     * @param  string                  $enumName       enum alias
     * @param  array<string, mixed>    $enumDefinition enum definition from a schema file
     * @throws InvalidSchemaDefinition if an enum has no values defined
     * @throws FileNotWritable         if a base file can't be opened with write permission
     * @throws FileNotWritable         if a user file can't be opened with write permission
     */
    private function generateEnum(string $enumName, array $enumDefinition): void
    {
        $definition = $this->getDefinition();
        $consts = null;

        $enumRealName = null;
        if (isset($enumDefinition['name']) === true) {
            $enumRealName = $enumDefinition['name'];
        } else {
            $enumRealName = Helper::codifyName($this->mapping) . '_' . Helper::codifyName($enumName);
        }

        if (isset($enumDefinition['values']) === false) {
            throw new InvalidSchemaDefinition('enum "' . $enumName . '" has no value');
        }
        foreach ($enumDefinition['values'] as $valueName => $valueAlias) {
            $valueRealName = null;
            if (\is_string($valueName) === true) {
                $valueRealName = $valueName;
            } else {
                $valueRealName = Helper::codifyName($enumName) . '_' . Helper::codifyName($valueAlias);
            }
            $consts .= '    const VALUE_' . \strtoupper(Helper::codifyName($valueAlias)) . ' = \'' . $enumRealName . '.' . $valueRealName . "';\n";
        }

        $mapping = Mapping::get($this->mapping);

        $path = \rtrim($mapping['config']['models']['path'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $path .= 'Base' . DIRECTORY_SEPARATOR . \ucfirst($enumName) . '.php';
        $file = \fopen($path, 'w+');
        if ($file === false) {
            throw new FileNotWritable('can\'t open "' . $path . '" with write permission');
        }
        $path .= 'Base' . DIRECTORY_SEPARATOR . \ucfirst($enumName) . '.php';
        $content = "<?php\n\nnamespace " . \ltrim(\rtrim($mapping['config']['models']['namespace'], '\\'), '\\') . "\\Base;\n\nuse RocknRoot\StrayFw\Database\Provider\Enum;\n";
        $content .= "\nclass " . \ucfirst($enumName) . " extends Enum\n{\n";
        $content .= "    public function getDatabaseName(): string\n    {\n        return '" . $mapping['config']['database'] . "';\n    }\n\n";
        $content .= "    public function getTableName(): string\n    {\n        return '" . $enumRealName . "';\n    }\n\n";
        $content .= $consts . "\n}";
        if (\fwrite($file, $content) === false) {
            throw new FileNotWritable('can\'t write in "' . $path . '"');
        }
        \fclose($file);

        $path = \rtrim($mapping['config']['models']['path'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $path .= \ucfirst($enumName) . '.php';
        if (\file_exists($path) === false) {
            $file = \fopen($path, 'w+');
            if ($file === false) {
                throw new FileNotWritable('can\'t open "' . $path . '" with write permission');
            }
            $content = "<?php\n\nnamespace " . \ltrim(\rtrim($mapping['config']['models']['namespace'], '\\'), '\\') . ";\n\nuse " . \rtrim($mapping['config']['models']['namespace'], '\\') . "\\Base\\" . \ucfirst($enumName) . " as BaseEnum;\n\nclass " . \ucfirst($enumName) . " extends BaseEnum\n{\n}";
            if (\fwrite($file, $content) === false) {
                throw new FileNotWritable('can\'t write in "' . $path . '"');
            }
        }

        echo $enumName . ' - Done' . PHP_EOL;
    }

    /**
     * Generate classes for a model.
     *
     * @param  string                  $modelName       model alias
     * @param  array<string, mixed>    $modelDefinition model definition from a schema file
     * @throws InvalidSchemaDefinition if a model has no field
     * @throws InvalidSchemaDefinition if a model is linked to an unknown enum
     * @throws InvalidSchemaDefinition if a model is linked to an unknown model
     * @throws InvalidSchemaDefinition if, while building a link, a model has an unknown needed field
     * @throws FileNotWritable         if a base file can't be opened with write permission
     * @throws FileNotWritable         if a user file can't be opened with write permission
     */
    private function generateModel(string $modelName, array $modelDefinition): void
    {
        $definition = $this->getDefinition();
        $primary = array();
        $constructor = '    public function __construct(array $fetch = null)' . "\n    {\n        parent::__construct();\n";
        $constructorDefaults = '        if (\is_array($fetch) === true && \count($fetch) > 0) {' . PHP_EOL . '            $this->new = false;' . "\n        } else {\n" . '            $fetch = array();' . "\n        }\n";
        $consts = null;
        $properties = null;
        $accessors = null;
        $allFieldsRealNames = "    public function getAllFieldsRealNames(): array\n    {\n        return [ ";
        $allFieldsAliases = "    public function getAllFieldsAliases(): array\n    {\n        return [ ";

        $modelRealName = null;
        if (isset($modelDefinition['name']) === true) {
            $modelRealName = $modelDefinition['name'];
        } else {
            $modelRealName = Helper::codifyName($this->mapping) . '_' . Helper::codifyName($modelName);
        }

        if (isset($modelDefinition['fields']) === false) {
            throw new InvalidSchemaDefinition('model "' . $modelName . '" has no field');
        }
        foreach ($modelDefinition['fields'] as $fieldName => $fieldDefinition) {
            $fieldRealName = null;
            if (isset($fieldDefinition['name']) === true) {
                $fieldRealName = $fieldDefinition['name'];
            } else {
                $fieldRealName = Helper::codifyName($modelName) . '_' . Helper::codifyName($fieldName);
            }

            $properties .= '    protected $field' . \ucfirst($fieldName) . ";\n";
            $consts .= '    public const FIELD_' . \strtoupper(Helper::codifyName($fieldName)) . ' = \'' . $modelRealName . '.' . $fieldRealName . "';\n";

            $constructor .= '        $this->field' . \ucfirst($fieldName) . ' = [ \'alias\' => \'' . $fieldName . "', 'value' => null ];\n";
            $constructorDefaults .= '        if (isset($fetch[\'' . $fieldRealName . '\']) === true) {' . "\n            ";
            $constructorDefaults .= '$this->set' . \ucfirst($fieldName) . '($fetch[\'' . $fieldRealName . "']);\n        } else {\n            ";
            $constructorDefaults .= '$this->set' . \ucfirst($fieldName) . '(';
            if (isset($fieldDefinition['default']) === true) {
                if (\is_bool($fieldDefinition['default']) === true) {
                    $constructorDefaults .= ($fieldDefinition['default'] === true) ? 'true' : 'false';
                } else {
                    $constructorDefaults .= '\'' . $fieldDefinition['default'] . '\'';
                }
            } else {
                $constructorDefaults .= 'null';
            }
            $constructorDefaults .= ");\n        }\n";

            if (isset($fieldDefinition['primary']) === true && $fieldDefinition['primary'] === true) {
                $primary[] = $fieldName;
            }

            $accessors .= '    public function get' . \ucfirst($fieldName) . "()\n    {\n        ";
            $valueType = '';
            switch ($fieldDefinition['type']) {
                case 'string':
                    $accessors .= 'return \stripslashes($this->field' . \ucfirst($fieldName) . '[\'value\']);';
                    $valueType = '?string ';
                    break;

                case 'char':
                    $accessors .= 'return \stripslashes($this->field' . \ucfirst($fieldName) . '[\'value\']);';
                    $valueType = '?string ';
                    break;

                case 'bool':
                    $accessors .= 'return \filter_var($this->field' . \ucfirst($fieldName) . '[\'value\'], FILTER_VALIDATE_BOOLEAN);';
                    $valueType = '?bool ';
                    break;

                case 'json':
                    $accessors .= 'return \json_decode($this->field' . \ucfirst($fieldName) . '[\'value\'], true);';
                    break;

                default:
                    $accessors .= 'return $this->field' . \ucfirst($fieldName) . '[\'value\'];';
                    break;
            }
            $accessors .= "\n    }\n\n";

            $accessors .= '    public function set' . \ucfirst($fieldName) . '(' . $valueType . '$value): void' . "\n    {\n        ";
            if ($fieldDefinition['type'] == 'bool') {
                $accessors .= '$this->field' . \ucfirst($fieldName) . '[\'value\'] = (bool) $value;';
            } elseif ($fieldDefinition['type'] == 'json') {
                $accessors .= 'if (\is_string($value) === true) {' . PHP_EOL;
                $accessors .= '            $this->field' . \ucfirst($fieldName) . '[\'value\'] = $value;' . PHP_EOL;
                $accessors .= '        } else {' . PHP_EOL;
                $accessors .= '            $this->field' . \ucfirst($fieldName) . '[\'value\'] = json_encode($value);' . PHP_EOL;
                $accessors .= '        }' . PHP_EOL;
            } else {
                $accessors .= '$this->field' . \ucfirst($fieldName) . '[\'value\'] = $value;';
            }
            $accessors .= PHP_EOL . '        $this->modified[\'' . $fieldName . '\'] = true;';
            $accessors .= "\n    }\n\n";

            $allFieldsRealNames .= '\'' . $modelRealName . '.' . $fieldRealName . '\', ';
            $allFieldsAliases .= '\'' . $fieldName . '\', ';
        }

        if (isset($modelDefinition['links']) === true) {
            foreach ($modelDefinition['links'] as $linkName => $linkDefinition) {
                if (isset($definition[$linkDefinition['model']]) === false) {
                    throw new InvalidSchemaDefinition('unknown model for link "' . $linkName . '" of model "' . $modelName . '"');
                }
                $linkedModel = $definition[$linkDefinition['model']];
                $accessors .= '    public function getLinked' . \ucfirst($linkName) . '(): Models\\' . \ucfirst($linkDefinition['model']) . "\n    {\n        ";
                $accessors .= 'return Models\\' . \ucfirst($linkDefinition['model']) . '::fetchEntity([ ';
                $links = array();
                foreach ($linkDefinition['fields'] as $from => $to) {
                    if (isset($modelDefinition['fields'][$from]) === false) {
                        throw new InvalidSchemaDefinition('building link : model "' . $modelName . '" has no field named "' . $from . '"');
                    }
                    if (isset($linkedModel['fields'], $linkedModel['fields'][$to]) === false) {
                        throw new InvalidSchemaDefinition('building link : model "' . $linkDefinition['model'] . '" has no field named "' . $to . '"');
                    }
                    $links[] = '\'' . $to . '\' => $this->get' . \ucfirst($from) . '()';
                }
                $accessors .= \implode(', ', $links) . " ]);\n    }\n\n";
            }
        }

        $allFieldsRealNames = \substr($allFieldsRealNames, 0, -2) . " ];\n    }\n\n";
        $allFieldsAliases = \substr($allFieldsAliases, 0, -2) . " ];\n    }\n\n";
        $constructor .= $constructorDefaults;
        $constructor .= PHP_EOL . '        $this->modified = array();' . PHP_EOL;
        $constructor .= "    }\n\n";

        $mapping = Mapping::get($this->mapping);

        $path = \rtrim($mapping['config']['models']['path'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $path .= 'Base' . DIRECTORY_SEPARATOR . \ucfirst($modelName) . '.php';
        $file = \fopen($path, 'w+');
        if ($file === false) {
            throw new FileNotWritable('can\'t open "' . $path . '" with write permission');
        }
        $content = "<?php\n\nnamespace " . \ltrim(\rtrim($mapping['config']['models']['namespace'], '\\'), '\\') . "\\Base;\n\nuse RocknRoot\StrayFw\Database\Postgres\Model;\n";
        $content .= "\nclass " . \ucfirst($modelName) . " extends Model\n{\n";
        $content .= "    public function getDatabaseName(): string\n    {\n        return '" . $mapping['config']['database'] . "';\n    }\n\n";
        $content .= "    public function getTableName(): string\n    {\n        return '" . $modelRealName . "';\n    }\n\n";
        $content .= $consts . PHP_EOL . $properties . PHP_EOL;
        $content .= $constructor . $accessors . $allFieldsRealNames . $allFieldsAliases;
        $content .= "    public function getPrimary(): array\n    {\n        return [ '" . \implode('\', \'', $primary) . "' ];\n    }\n";
        $content .= "}";
        if (\fwrite($file, $content) === false) {
            throw new FileNotWritable('can\'t write in "' . $path . '"');
        }
        \fclose($file);

        $path = \rtrim($mapping['config']['models']['path'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $path .= \ucfirst($modelName) . '.php';
        if (\file_exists($path) === false) {
            $file = \fopen($path, 'w+');
            if ($file === false) {
                throw new FileNotWritable('can\'t open "' . $path . '" with write permission');
            }
            $content = "<?php\n\nnamespace " . \ltrim(\rtrim($mapping['config']['models']['namespace'], '\\'), '\\') . ";\n\nuse " . \ltrim(\rtrim($mapping['config']['models']['namespace'], '\\'), '\\') . "\\Base\\" . \ucfirst($modelName) . " as BaseModel;\n\nclass " . \ucfirst($modelName) . " extends BaseModel\n{\n}";
            if (\fwrite($file, $content) === false) {
                throw new FileNotWritable('can\'t write in "' . $path . '"');
            }
        }

        echo $modelName . ' - Done' . PHP_EOL;
    }
}
