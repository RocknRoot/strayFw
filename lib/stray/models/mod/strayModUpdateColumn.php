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
  $statement = 'ALTER TABLE ' . $table;
  if ($column != $schema['name'])
    $statement .= ' RENAME COLUMN ' . $column . ' TO ' . $schema['name'] . ',';
  $create = strayfModCreateColumn($schema);
  $create = substr_replace($create, ' TYPE ', strpos($create, ' '), 1);
  if (false !== strpos($create, 'NOT NULL'))
  {
    $create = str_replace('NOT NULL', null, $create);
    $statement .= ' ALTER COLUMN ' . $create . ', ALTER COLUMN ' . $schema['name'] . ' SET NOT NULL;';
  }
  else
  {
    $statement .= ' ALTER COLUMN ' . $create . ';';
  }
  return $statement;
}
