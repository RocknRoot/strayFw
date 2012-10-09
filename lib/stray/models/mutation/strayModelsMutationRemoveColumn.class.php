<?php
/**
 * @brief Mutation for column removing.
 * @author nekith@gmail.com
 */
class strayModelsMutationRemoveColumn extends strayModelsAMutation
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
   * @param string $column existing column alias
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
      throw new strayExceptionFatal('MutationRemoveColumn : table is empty');
    if (null ==  $this->_column)
      throw new strayExceptionFatal('MutationRemoveColumn : column is empty');
    $column_name = $this->_migration->GetSchema()->{$this->_table}->columns->{$this->_column}->name;
    $sql = strayfModRemoveColumn(call_user_func(array('Model' . $this->_table, 'fGetName')), $column_name);
    $ret = $this->_migration->GetDb()->Execute($sql);
    if (true !== $ret)
      throw new strayExceptionError('MutationRemoveColumn SQL error : ' . $ret . ' (' . $sql . ')');
  }

  /**
   * Rollback the changes done by the mutation.
   */
  public function Rewind()
  {
    if (null == $this->_table)
      throw new strayExceptionFatal('MutationRemoveColumn : table is empty');
    if (null ==  $this->_column)
      throw new strayExceptionFatal('MutationRemoveColumn : column is empty');
    $schema = $this->_migration->GetSchema()->{$this->_table}->columns->{$this->_column};
    $sql = strayfModCreateColumn($schema);
    $sql = 'ALTER TABLE `' . call_user_func(array('Model' . $this->_table, 'fGetName')) . '` ADD COLUMN ' . $sql . ';';
    $ret = $this->_migration->GetDb()->Execute($sql);
    if (true !== $ret)
      throw new strayExceptionError('MutationRemoveColumn SQL error : ' . $ret . ' (' . $sql . ')');
  }
}