<?php
/**
 * Generate code for type creation.
 * @param array $schema column schema
 * @return string SQL query
 */

function strayfModCreateType(array $schema)
{
  $statement = null;
  if ($schema['type'] == 'enum')
  {
    $statement = 'CREATE TYPE t_' . $schema['name'] . ' AS ENUM(';
    foreach ($schema['values'] as $val)
      $statement .= '\'' . $val . '\', ';
    $statement = substr($statement, 0, -2) . ');';
  }
  return $statement;
}
