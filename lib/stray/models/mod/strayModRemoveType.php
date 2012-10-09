<?php
/**
 * Generate code for type deletion.
 * @param stdClass $schema column schema
 * @return string SQL query
 */

function strayfModRemoveType(stdClass $schema)
{
  $statement = null;
  if ($schema->type == 'enum')
    $statement = 'DROP TYPE IF EXISTS t_' . $schema->name . ';';
  return $statement;
}