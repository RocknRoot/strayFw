<?php
/**
 * Generate code for type creation.
 * @param stdClass $schema column schema
 * @return string SQL query
 */

function strayfModCreateType(stdClass $schema)
{
  $statement = null;
  if ($schema->type == 'enum')
  {
    $statement = 'CREATE TYPE t_' . $schema->name . ' AS ENUM(';
    foreach ($schema->values as $val)
      $statement .= '\'' . $val . '\', ';
    $statement = substr($statement, 0, -2) . ');';
  }
  return $statement;
}
