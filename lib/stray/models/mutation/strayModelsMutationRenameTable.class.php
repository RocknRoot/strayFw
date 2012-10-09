<?php
/**
 * @brief Mutation for table renaming.
 * @author nekith@gmail.com
 */
class strayModelsMutationRenameTable extends strayModelsAMutation
{
  /**
   * Table old alias.
   * @var string
   */
  protected $_table;
  /**
   * Table new alias.
   * @var string
   */
  protected $_newTable;

  /**
   * Constructor.
   * @param string $table table old alias
   * @param string $table_new table new alias
   */
  public function __construct($table, $table_new)
  {
    $this->_table = $table;
    $this->_newTable = $table_new;
  }

  /**
   * Run the mutation and apply changes.
   */
  public function Execute()
  {
    if (null == $this->_table)
      throw new strayExceptionFatal('MutationRenameTable : table is empty');
    if (null == $this->_newTable)
      throw new strayExceptionFatal('MutationRenameTable : new table is empty');
    $table = $this->_migration->GetSchema()->{$this->_table}->name;
    $new_table = $this->_migration->GetForwardSchema()->{$this->_newTable}->name;
    $sql = strayfModRenameTable($table, $new_table);
    $ret = $this->_migration->GetDb()->Execute($sql);
    if (true !== $ret)
      throw new strayExceptionError('MutationRenameTable SQL error : ' . $ret . ' (' . $sql . ')');
  }

  /**
   * Rollback the changes done by the mutation.
   */
  public function Rewind()
  {
    if (null == $this->_table)
      throw new strayExceptionFatal('MutationRenameTable : table is empty');
    if (null == $this->_newTable)
      throw new strayExceptionFatal('MutationRenameTable : new table is empty');
    $table = $this->_migration->GetSchema()->{$this->_table}->name;
    $new_table = $this->_migration->GetForwardSchema()->{$this->_newTable}->name;
    $sql = strayfModRenameTable($new_table, $table);
    $ret = $this->_migration->GetDb()->Execute($sql);
    if (true !== $ret)
      throw new strayExceptionError('MutationRenameTable SQL error : ' . $ret . ' (' . $sql . ')');
  }
}