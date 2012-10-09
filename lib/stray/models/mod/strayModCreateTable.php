<?php
/**
 * Generate code for table creation.
 * @param stdClass $table table table
 * @param stdClass $schema db table
 * @return string SQL query
 */

function strayfModCreateTable(stdClass $table, stdClass $schema)
{
  $statement = 'CREATE TABLE ' . $table->name . ' (';
  if (false === isset($table->columns))
    throw new strayExceptionError('Syntax error in table : table has no columns');
  $primary = array();
  foreach ($table->columns as $key => $elem)
  {
    $statement .= strayfModCreateColumn($elem);
    // primary
    if (true === isset($elem->primary))
    {
      if (false === is_bool($elem->primary))
        throw new strayExceptionError('Syntax error in table : primary value isn\'t boolean');
      if ($elem->primary === true)
        $primary[] = $key;
    }
    $statement .= ', ';
  }
  if (count($primary) >= 1)
  {
    $statement .= 'CONSTRAINT pk_' . $table->name . ' PRIMARY KEY (';
    foreach ($primary as $elem)
      $statement .= $table->columns->$elem->name . ', ';
      $statement = substr($statement, 0, -2) . '), ';
  }
  else if (false === isset($table->inherits))
    throw new strayExceptionError('No primary key for ' . $table->name);
  $statement = substr($statement, 0, -2) . ')';
  if (true === isset($table->inherits))
    $statement .= ' INHERITS (' . $schema->{$table->inherits}->name . ')';
  $statement .= ';';
  return $statement;
}
