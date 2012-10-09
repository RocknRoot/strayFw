<?php
/**
 * @brief Abstract class for migration elements.
 * @abstract
 * @author nekith@gmail.com
 */
abstract class strayModelsAMutation
{
  /**
   * Migration instance.
   * @var strayModelsAMigration
   */
  protected $_migration;

  /**
   * Set the associated mutation instance.
   * @param strayModelsAMigration $migration migration instance
   */
  public function SetMigration(strayModelsAMigration $migration)
  {
    $this->_migration = $migration;
  }

  /**
   * Run the mutation and apply changes.
   */
  abstract public function Execute();

  /**
   * Rollback the changes done by the mutation.
   */
  abstract public function Rewind();
}
