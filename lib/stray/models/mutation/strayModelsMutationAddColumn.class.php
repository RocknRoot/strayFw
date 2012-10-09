<?php
/**
 * @brief Mutation for column adding.
 * @author nekith@gmail.com
 */
class strayModelsMutationAddColumn extends strayModelsAMutation
{
  /**
   * Table alias.
   * @var string
   */
  protected $_table;

  /**
   * Column alias.
   * @var string
   */
  protected $_column;

  /**
   * Constructor.
   * @param string $table existing table alias
   * @param string $column column alias
   */
  public function __construct($table, $column)
  {
    $this->_table = $table;
    $this->_column = $column;
  }

  /**
   * Run the mutation and apply changes.
   */
  public function Execute()
  {
    if (null == $this->_table)
      throw new strayExceptionFatal('MutationAddColumn : table is empty');
    if (null == $this->_column)
      throw new strayExceptionFatal('MutationAddColumn : column is empty');
    $definition = $this->_migration->GetForwardSchema()->{$this->_table}->columns->{$this->_column};
    $sql = strayfModCreateColumn($definition);
    $sql = 'ALTER TABLE ' . call_user_func(array('Model' . $this->_table, 'fGetName')) . ' ADD COLUMN ' . $sql . ';';
    $ret = $this->_migration->GetDb()->Execute($sql);
    if (true !== $ret)
      throw new strayExceptionError('MutationAddColumn SQL error : ' . $ret . ' (' . $sql . ')');
    // foreign keys
    $sql = strayfModCreateForeignKeys($this->_migration->GetForwardSchema()->{$this->_table}, $this->_migration->GetForwardSchema());
    $ret = $this->_migration->GetDb()->Execute($sql);
    if (true !== $ret)
      echo 'MutationAddColumn SQL notice : ' . $ret . ' (' . $sql . ')';
  }

  /**
   * Rollback the changes done by the mutation.
   */
  public function Rewind()
  {
    if (null == $this->_table)
      throw new strayExceptionFatal('MutationAddColumn : table is empty');
    if (null == $this->_column)
      throw new strayExceptionFatal('MutationAddColumn : column is empty');
    $column_name = $this->_migration->GetForwardSchema()->{$this->_table}->columns->{$this->_column}->name;
    $sql = strayfModRemoveColumn(call_user_func(array('Model' . $this->_table, 'fGetName')), $column_name);
    $ret = $this->_migration->GetDb()->Execute($sql);
    if (true !== $ret)
      throw new strayExceptionError('MutationAddColumn SQL error : ' . $ret . ' (' . $sql . ')');
  }
}
