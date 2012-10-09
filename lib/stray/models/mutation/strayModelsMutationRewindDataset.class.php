<?php
/**
 * @brief Mutation for dataset rewinding.
 * @author nekith@gmail.com
 */
class strayModelsMutationRewindDataset extends strayModelsAMutation
{
  /**
   * Dataset name.
   * @var string
   */
  protected $_dataset;

  /**
   * Constructor.
   * @param string $name dataset name
   */
  public function __construct($dataset)
  {
    $this->_dataset = $dataset;
  }

  /**
   * Run the mutation and apply changes.
   */
  public function Execute()
  {
    if (null == $this->_dataset)
      throw new strayExceptionFatal('MutationExecuteDataset : dataset is empty');
    $dir = $this->_migration->GetDbConfig()->Path() . '/datasets/' . $this->_dataset . '/';
    $path = $dir . '/' . ucfirst($this->_dataset) . '.dataset.php';
    require_once $path;
    $class = 'Dataset' . ucfirst($this->_dataset);
    $dataset = new $class($dir);
    $dataset->Rewind();
  }

  /**
   * Rollback the changes done by the mutation.
   */
  public function Rewind()
  {
    if (null == $this->_dataset)
      throw new strayExceptionFatal('MutationExecuteDataset : dataset is empty');
    $dir = $this->_migration->GetDbConfig()->Path() . '/datasets/' . $this->_dataset . '/';
    $path = $dir . '/' . ucfirst($this->_dataset) . '.dataset.php';
    require_once $path;
    $class = 'Dataset' . ucfirst($this->_dataset);
    $dataset = new $class($dir);
    $dataset->Execute();
  }
}