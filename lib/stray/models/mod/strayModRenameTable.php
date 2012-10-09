<?php
/**
 * Generate code for table renaming.
 * @param string $table table name
 * @param string $new_table new table name
 * @return string SQL query
 */

function strayfModRenameTable($table, $new_table)
{
  $statement = 'ALTER TABLE `' . $table . '` RENAME TO `' . $new_table . '`;';
  return $statement;
}