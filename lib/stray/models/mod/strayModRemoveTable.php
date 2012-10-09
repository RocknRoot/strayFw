<?php
/**
 * Generate code for table removing
 * @param string $table table name
 * @return string SQL query
 */

function strayfModRemoveTable($table)
{
  $statement = 'DROP TABLE IF EXISTS ' . $table . ' CASCADE;';
  return $statement;
}