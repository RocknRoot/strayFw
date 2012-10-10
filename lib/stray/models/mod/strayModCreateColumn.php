<?php
/**
 * Generate code for column creation.
 * @param array $schema table schema
 * @return string SQL query
 */

function strayfModCreateColumn(array $schema)
{
  $statement = null;
  switch ($schema['type'])
  {
    case 'bool':
      $statement .= $schema['name'] . ' BOOL';
      if (true === isset($schema['default']))
      {
        if (false === is_bool($schema['default']))
          throw new strayExceptionError('Syntax error in schema : default value for boolean isn\'t boolean');
        $statement .= ' DEFAULT ';
        $statement .= true === $schema['default'] ? 'TRUE' : 'FALSE';
      }
      break;
    case 'char':
      $statement .= $schema['name'] . ' CHAR('
          . (true === isset($schema['size']) ? $schema['size'] : '45') . ')';
      if (true === isset($schema['default']))
        $statement .= ' DEFAULT \'' . $schema['default'] . '\'';
      break;
    case 'string':
      $statement .= $schema['name'] . ' VARCHAR('
          . (true === isset($schema['size']) ? $schema['size'] : '45') . ')';
      if (true === isset($schema['default']))
        $statement .= ' DEFAULT \'' . $schema['default'] . '\'';
      break;
    case 'enum':
      $statement .= $schema['name'] . ' t_' . $schema['name'];
      break;
    case 'serial':
      $statement .= $schema['name'] . ' SERIAL';
      break;
    case 'int':
      $statement .= $schema['name'] . ' INT';
      if (true === isset($schema['default']))
        $statement .= ' DEFAULT ' . $schema['default'];
      break;
    case 'tinyint':
      $statement .= $schema['name'] . ' TINYINT';
      if (true === isset($schema['default']))
        $statement .= ' DEFAULT ' . $schema['default'];
      break;
    case 'smallint':
      $statement .= $schema['name'] . ' SMALLINT';
      if (true === isset($schema['default']))
        $statement .= ' DEFAULT ' . $schema['default'];
      break;
    case 'float':
      $statement .= $schema['name'] . ' FLOAT';
      if (true === isset($schema['default']))
        $statement .= ' DEFAULT ' . $schema['default'];
      break;
    case 'decimal':
      $statement .= $schema['name'] . ' NUMBER(' . $schema['size'] . ','
          . $schema['scale'] . ')';
      break;
    case 'timestamp':
      $statement .= $schema['name'] . ' TIMESTAMP';
      if (true === isset($schema['default']))
        $statement .= ' DEFAULT ' . $schema['default'];
      break;
    case 'text':
      $statement .= $schema['name'] . ' TEXT';
      break;
    case 'blob':
      $statement .= $schema['name'] . ' BLOB';
      break;
    case 'bit':
      $statement .= $schema['name'] . ' BIT';
      if (true === isset($schema['size']))
        $statement .= ' (' . $schema['size'] . ')';
      break;
    case 'bitstring':
      $statement .= $schema['name'] . ' BIT VARYING';
      if (true === isset($schema['size']))
        $statement .= ' (' . $schema['size'] . ')';
      break;
  }
  // not null
  if (true === isset($schema['notnull']))
  {
    if (false === is_bool($schema['notnull']))
      throw new strayExceptionError('Syntax error in schema : notnull value isn\'t boolean');
    if ($schema['notnull'] === true)
      $statement .= ' NOT NULL';
  }
  return $statement;
}
