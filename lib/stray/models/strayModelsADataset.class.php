<?php
/**
 * @brief Abstract class for datasets.
 * @abstract
 * @author nekith@gmail.com
 */
abstract class strayModelsADataset
{
  /**
   * Migration directory path.
   * @var string
   */
  protected $_path;

  /**
   * Constructor.
   * @param string $path dir path
   */
  public function __construct($path)
  {
    $this->_path = $path;
  }

  /**
   * Run the dataset and apply changes.
   * @param array $params dataset parameters
   * @param array $options dataset options
   * @return bool true will commit modifications
   */
  abstract public function Execute(array $params = null, array $options = null);

  /**
   * Rollback the changes done by the dataset.
   * @param array $params dataset parameters
   * @param array $options dataset options
   * @return bool true will commit modifications
   */
  abstract public function Rewind(array $params = null, array $options = null);

  /**
   * User-side method for displaying dataset help screen.
   */
  abstract public function Help();

}
