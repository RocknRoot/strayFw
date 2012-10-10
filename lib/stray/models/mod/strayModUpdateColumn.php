<?php
/**
 * Generate code for column updating.
 * @param string $table table name
 * @param string $column column name
 * @param array $schema new column schema
 * @return string SQL query
 */

function strayfModUpdateColumn($table, $column, array $schema)
{
  $statement = 'ALTER TABLE `' . $table . '` CHANGE ' . $column . ' ' . strayfModCreateColumn($schema) . ';';
  return $statement;
}
