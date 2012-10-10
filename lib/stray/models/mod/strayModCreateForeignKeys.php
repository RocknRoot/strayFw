<?php
/**
 * Generate SQL code for foreign keys creation.
 * @param array $table table schema
 * @param array $schema database schema
 * @return string SQL query
 */
function strayfModCreateForeignKeys(array $table, array $schema)
{
  if (false === isset($table['foreign']))
    return;
  $sql = 'ALTER TABLE ' . $table['name'] . '';
  foreach ($table['foreign'] as $fname => $foreign)
  {
    $sql .= strayfModCreateForeignKey($fname, $table, $schema);
    $sql .= ', ';
  }
  $sql = substr($sql, 0, -2) . ';';
  return $sql;
}
