<?php
/**
 * Generate code for column deletion.
 * @param string $table table name
 * @param string $column column name
 * @return string SQL query
 */

function strayfModRemoveColumn($table, $column)
{
  $statement = 'ALTER TABLE `' . $table . '` DROP COLUMN ' . $column . ';';
  return $statement;
}