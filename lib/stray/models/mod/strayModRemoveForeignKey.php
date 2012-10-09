<?php
/**
 * Generate SQL code for foreign key deletion.
 * @param string $table table name
 * @param string $key foreign key name
 * @return string SQL query
 */
function strayfModRemoveForeignKey($table, $key)
{
  $sql = 'ALTER TABLE ' . $table . ' DROP CONSTRAINT IF EXISTS ' . $key . ';';
  return $sql;
}
