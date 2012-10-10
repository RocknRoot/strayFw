<?php
/**
 * Generate code for type deletion.
 * @param array $schema column schema
 * @return string SQL query
 */

function strayfModRemoveType(array $schema)
{
  $statement = null;
  if ($schema['type'] == 'enum')
    $statement = 'DROP TYPE IF EXISTS t_' . $schema['name'] . ';';
  return $statement;
}
