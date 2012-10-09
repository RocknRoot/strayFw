<?php
/**
 * @brief Mutation for column modifiying.
 * @author nekith@gmail.com
 */
class strayModelsMutationModifyColumn extends strayModelsAMutation
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
   * Column new alias.
   * @var string
   */
  protected $_newColumn;

  /**
   * Constructor.
   * @param string $table existing table alias
   * @param string $column existing column alias
   * @param string $new_column new column alias
   */
  public function __construct($table, $column, $new_column)
  {
    $this->_table = $table;
    $this->_column = $column;
    $this->_newColumn = $new_column;
  }

  /**
   * Run the mutation and apply changes.
   */
  public function Execute()
  {
    if (null == $this->_table)
      throw new strayExceptionFatal('MutationModifyColumn : table is empty');
    if (null ==  $this->_column)
      throw new strayExceptionFatal('MutationModifyColumn : column is empty');
    if (null ==  $this->_newColumn)
      throw new strayExceptionFatal('MutationModifyColumn : new column is empty');
    $definition = $this->_migration->GetForwardSchema()->{$this->_table}->columns->{$this->_newColumn};
    $column_name = $this->_migration->GetSchema()->{$this->_table}->columns->{$this->_column}->name;
    $sql = strayfModUpdateColumn(call_user_func(array('Model' . $this->_table, 'fGetName')), $column_name, $definition);
    $ret = $this->_migration->GetDb()->Execute($sql);
    if (true !== $ret)
      throw new strayExceptionError('MutationModifyColumn SQL error : ' . $ret . ' (' . $sql . ')');
  }

  /**
   * Rollback the changes done by the mutation.
   */
  public function Rewind()
  {
    if (null == $this->_table)
      throw new strayExceptionFatal('MutationModifyColumn : table is empty');
    if (null ==  $this->_column)
      throw new strayExceptionFatal('MutationModifyColumn : column is empty');
    if (null ==  $this->_newColumn)
      throw new strayExceptionFatal('MutationModifyColumn : new column is empty');
    $schema = $this->_migration->GetSchema()->{$this->_table}->columns->{$this->_column};
    $column_name = $this->_migration->GetForwardSchema()->{$this->_table}->columns->{$this->_newColumn}->name;
    $sql = strayfModUpdateColumn(call_user_func(array('Model' . $this->_table, 'fGetName')), $column_name, $schema);
    $ret = $this->_migration->GetDb()->Execute($sql);
    if (true !== $ret)
      throw new strayExceptionError('MutationModifyColumn SQL error : ' . $ret . ' (' . $sql . ')');
  }
}