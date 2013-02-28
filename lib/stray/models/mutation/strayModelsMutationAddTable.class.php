<?php
/**
 * @brief Mutation for table adding.
 * @author nekith@gmail.com
 */
class strayModelsMutationAddTable extends strayModelsAMutation
{
  /**
   * Table alias.
   * @var string
   */
  protected $_table;

  /**
   * Constructor.
   * @param string $table table alias
   */
  public function __construct($table)
  {
    $this->_table = $table;
  }

  /**
   * Run the mutation and apply changes.
   */
  public function Execute()
  {
    if (null == $this->_table)
      throw new strayExceptionFatal('MutationAddTable : table is empty');
    $schema = $this->_migration->GetForwardSchema();
    $definition = $schema[$this->_table];
    $sql = strayfModCreateTable($definition, $schema);
    $ret = $this->_migration->GetDb()->Execute($sql);
    if (true !== $ret)
      throw new strayExceptionError('MutationAddTable SQL error : ' . $ret . ' (' . $sql . ')');
    $sql = strayfModCreateForeignKeys($schema[$this->_table], $schema);
    if (null != $sql)
    {
      $ret = $this->_migration->GetDb()->Execute($sql);
      if (true !== $ret)
        throw new strayExceptionError('MutationAddTable SQL error : ' . $ret . ' (' . $sql . ')');
    }
  }

  /**
   * Rollback the changes done by the mutation.
   */
  public function Rewind()
  {
    if (null == $this->_table)
      throw new strayExceptionFatal('MutationAddTable : table is empty');
    $schema = $this->_migration->GetForwardSchema();
    if (true === isset($schema[$this->_table]['foreign']))
    {
      $keys = $schema[$this->_table]['foreign'];
      foreach ($keys as $key => $null)
      {
        $sql = strayfModRemoveForeignKey($schema[$this->_table]['name'], $key);
        $ret = $this->_migration->GetDb()->Execute($sql);
        if (true !== $ret)
          throw new strayExceptionError('MutationAddTable SQL error : ' . $ret . ' (' . $sql . ')');
      }
    }
    $sql = strayfModRemoveTable($schema[$this->_table]['name']);
    $ret = $this->_migration->GetDb()->Execute($sql);
    if (true !== $ret)
      throw new strayExceptionError('MutationAddTable SQL error : ' . $ret . ' (' . $sql . ')');
  }
}
