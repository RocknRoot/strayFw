<?php
/**
 * Generate SQL code for foreign key creation.
 * @param string $fkname foreign key name
 * @param array $table table schema
 * @param array $schema database schema
 * @return string SQL query
 */
function strayfModCreateForeignKey($fkname, array $table, array $schema)
{
  if (false === isset($table['foreign'][$fkname]))
    return null;
  $foreign = $table['foreign'][$fkname];
  $colsA = array();
  $colsB = array();
  foreach ($foreign['columns'] as $key => $col)
  {
    $colsA[] = $table['columns'][$key]['name'];
    $colsB[] = $schema[$foreign['table']]['columns'][$col]['name'];
  }
  $sql = ' ADD CONSTRAINT ' . $fkname . ' FOREIGN KEY ('
    . implode(', ', $colsA) . ') REFERENCES '
    . $schema[$foreign['table']]['name'] . '('
    . implode(', ', $colsB) . ')';
  if (true === isset($foreign['update']))
    $sql .= ' ON UPDATE ' . $foreign['update'];
  if (true === isset($foreign['delete']))
    $sql .= ' ON DELETE ' . $foreign['delete'];
  return $sql;
}
