<?php
/**
 * Generate SQL code for foreign keys creation.
 * @param stdClass $table table schema
 * @param stdClass $schema database schema
 * @return string SQL query
 */
function strayfModCreateForeignKeys(stdClass $table, stdClass $schema)
{
  if (false === isset($table->foreign))
    return;
  $sql = 'ALTER TABLE ' . $table->name . '';
  foreach ($table->foreign as $fname => $foreign)
  {
    $sql .= strayfModCreateForeignKey($fname, $table, $schema);
    $sql .= ', ';
  }
  $sql = substr($sql, 0, -2) . ';';
  return $sql;
}
