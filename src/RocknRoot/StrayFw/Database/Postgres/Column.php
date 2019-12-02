<?php

namespace RocknRoot\StrayFw\Database\Postgres;

use RocknRoot\StrayFw\Database\Helper;
use RocknRoot\StrayFw\Exception\InvalidSchemaDefinition;

/**
 * Column helper functions.
 *
 * @abstract
 *
 * @author Nekith <nekith@errant-works.com>
 */
abstract class Column
{
    /**
     * Generate a column SQL fieldDefinition.
     *
     * @param  array                   $schema          schema definition
     * @param  string                  $mapping         mapping name
     * @param  string                  $fieldName       field real name
     * @param  array                   $fieldDefinition field fieldDefinition
     * @throws InvalidSchemaDefinition if default value aren't well typed
     * @throws InvalidSchemaDefinition if a field has an unknown type
     * @return string                  generated SQL
     */
    public static function generateDefinition(array $schema, string $mapping, string $fieldName, array $fieldDefinition) : string
    {
        $sql = $fieldName . ' ';
        switch ($fieldDefinition['type']) {
        case 'bool':
            $sql .= 'BOOL';
            if (isset($fieldDefinition['default']) === true) {
                if (is_bool($fieldDefinition['default']) === false) {
                    throw new InvalidSchemaDefinition('default value for "' . $fieldName . '" isn\'t a boolean');
                }
                $sql .= ' DEFAULT ' . ($fieldDefinition['default'] === true ? 'TRUE' : 'FALSE');
            }
            break;

        case 'char':
            $sql .= 'CHAR(' . (isset($fieldDefinition['size']) === true ? $fieldDefinition['size'] : 45) . ')';
            if (isset($fieldDefinition['default']) === true) {
                $sql .= ' DEFAULT \'' . $fieldDefinition['default'] . '\'';
            }
            break;

        case 'string':
            $sql .= 'VARCHAR(' . (isset($fieldDefinition['size']) === true ? $fieldDefinition['size'] : 45) . ')';
            if (isset($fieldDefinition['default']) === true) {
                $sql .= ' DEFAULT \'' . $fieldDefinition['default'] . '\'';
            }
            break;

        case 'serial':
            $sql .= 'SERIAL';
            break;

        case 'bigserial':
            $sql .= 'BIGSERIAL';
            break;

        case 'int':
            $sql .= 'INT';
            if (isset($fieldDefinition['default']) === true) {
                $sql .= ' DEFAULT \'' . $fieldDefinition['default'] . '\'';
            }
            break;

        case 'smallint':
            $sql .= 'SMALLINT';
            if (isset($fieldDefinition['default']) === true) {
                $sql .= ' DEFAULT \'' . $fieldDefinition['default'] . '\'';
            }
            break;

        case 'float':
            $sql .= 'FLOAT';
            if (isset($fieldDefinition['default']) === true) {
                $sql .= ' DEFAULT \'' . $fieldDefinition['default'] . '\'';
            }
            break;

        case 'numeric':
        case 'decimal':
            $sql .= 'NUMERIC';
            if (isset($fieldDefinition['precision']) === true) {
                $sql .= '(' . $fieldDefinition['precision'];
                if (isset($fieldDefinition['scale']) === true) {
                    $sql .= ', ' . $fieldDefinition['scale'];
                }
                $sql .= ')';
            }
            break;

        case 'timestamp':
            $sql .= 'TIMESTAMP';
            if (isset($fieldDefinition['default']) === true) {
                if ($fieldDefinition['default'] == 'now') {
                    $sql .= ' DEFAULT CURRENT_TIMESTAMP';
                } else {
                    $sql .= ' DEFAULT \'' . $fieldDefinition['default'] . '\'';
                }
            }
            break;

        case 'text':
            $sql .= 'TEXT';
            break;

        case 'json':
            $sql .= 'JSON';
            break;

        case 'blob':
            $sql .= 'BLOB';
            break;

        case 'bit':
            $sql .= 'BIT';
            if (isset($fieldDefinition['size']) === true) {
                $sql .= '(' . $fieldDefinition['size'] . ')';
            }
            break;

        case 'bitstring':
            $sql .= 'BIT VARYING';
            if (isset($fieldDefinition['size']) === true) {
                $sql .= '(' . $fieldDefinition['size'] . ')';
            }
            break;

        default:
            $type = $fieldDefinition['type'];
            if (isset($schema[$type]) === true) {
                if ($schema[$type]['type'] == 'enum') {
                    $enumRealName = null;
                    if (isset($schema[$type]['name']) === true) {
                        $enumRealName = $schema[$type]['name'];
                    } else {
                        $enumRealName = Helper::codifyName($mapping) . '_' . Helper::codifyName($type);
                    }
                    $sql .= $enumRealName;
                    break;
                }
            }
            throw new InvalidSchemaDefinition('field "' . $fieldName . '" has an unknown type');
            break;
        }
        if (isset($fieldDefinition['notnull']) === false || $fieldDefinition['notnull'] === true) {
            $sql .= ' NOT NULL';
        }

        return $sql;
    }
}
