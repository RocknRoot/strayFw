<?php
/**
 * @brief Mutation for foreign key adding.
 * @author nekith@gmail.com
 */
class strayModelsMutationAddForeignKey extends strayModelsAMutation
{
  /**
   * Table alias.
   * @var string
   */
  protected $_table;

  /**
   * Foreign key name.
   * @var string
   */
  protected $_fkname;

  /**
   * Constructor.
   * @param string $table existing table alias
   * @param string $fkname foreign key name
   */
  public function __construct($table, $fkname)
  {
    $this->_table = $table;
    $this->_fkname = $fkname;
  }

  /**
   * Run the mutation and apply changes.
   */
  public function Execute()
  {
    if (null == $this->_fkname)
      throw new strayExceptionFatal('MutationAddForeignKey : fkname is empty');
    if (null == $this->_table)
      throw new strayExceptionFatal('MutationAddForeignKey : table is empty');
    $sql = strayfModCreateForeignKey($this->_fkname, $this->_migration->GetForwardSchema()->{$this->_table}, $this->_migration->GetForwardSchema());
    $sql = 'ALTER TABLE ' . call_user_func(array('Model' . $this->_table, 'fGetName')) . ' ' . $sql . ';';
    $ret = $this->_migration->GetDb()->Execute($sql);
    if (true !== $ret)
      throw new strayExceptionError('MutationAddForeignKey SQL error : ' . $ret . ' (' . $sql . ')');
  }

  /**
   * Rollback the changes done by the mutation.
   */
  public function Rewind()
  {
    if (null == $this->_fkname)
      throw new strayExceptionFatal('MutationAddForeignKey : fkname is empty');
    if (null == $this->_table)
      throw new strayExceptionFatal('MutationAddForeignKey : table is empty');
    $sql = strayfModRemoveForeignKey(call_user_func(array('Model' . $this->_table, 'fGetName')), $this->_fkname);
    $ret = $this->_migration->GetDb()->Execute($sql);
    if (true !== $ret)
      throw new strayExceptionError('MutationAddForeignKey SQL error : ' . $ret . ' (' . $sql . ')');
  }
}
