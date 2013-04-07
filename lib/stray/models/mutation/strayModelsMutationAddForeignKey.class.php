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
  protected $_fkName;

  /**
   * Constructor.
   * @param string $table existing table alias
   * @param string $fkName foreign key name
   */
  public function __construct($table, $fkName)
  {
    $this->_table = $table;
    $this->_fkName = $fkName;
  }

  /**
   * Run the mutation and apply changes.
   */
  public function Execute()
  {
    if (null == $this->_fkName)
      throw new strayExceptionFatal('MutationAddForeignKey : fkName is empty');
    if (null == $this->_table)
      throw new strayExceptionFatal('MutationAddForeignKey : table is empty');
    $sql = strayfModCreateForeignKey($this->_fkName, $this->_migration->GetForwardSchema()[$this->_table], $this->_migration->GetForwardSchema());
    $sql = 'ALTER TABLE ' . $this->_migration->GetForwardSchema()[$this->_table]['name'] . ' ' . $sql . ';';
    $ret = $this->_migration->GetDb()->Execute($sql);
    if (true !== $ret)
      throw new strayExceptionError('MutationAddForeignKey SQL error : ' . $ret . ' (' . $sql . ')');
  }

  /**
   * Rollback the changes done by the mutation.
   */
  public function Rewind()
  {
    if (null == $this->_fkName)
      throw new strayExceptionFatal('MutationAddForeignKey : fkName is empty');
    if (null == $this->_table)
      throw new strayExceptionFatal('MutationAddForeignKey : table is empty');
    $sql = strayfModRemoveForeignKey($this->_migration->GetForwardSchema()[$this->_table]['name'], $this->_fkName);
    $ret = $this->_migration->GetDb()->Execute($sql);
    if (true !== $ret)
      throw new strayExceptionError('MutationAddForeignKey SQL error : ' . $ret . ' (' . $sql . ')');
  }
}
