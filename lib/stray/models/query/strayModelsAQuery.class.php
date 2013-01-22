<?php
/**
 * @brief Models' query abstract class.
 * @author nekith@gmail.com
 * @abstract
 */

abstract class strayModelsAQuery
{
  /**
   * Attached database.
   * @var strayModelsDatabase
   */
  protected $_db;
  /**
   * Query arguments.
   * @var array
   */
  protected $_args;
  /**
   * PDO query object.
   * @var PDOStatement
   */
  protected $_query;
  /**
   * PDO error info.
   * @var array
   */
  protected $_queryError = array();
  /**
   * Error informations.
   * @var array
   */
  protected $_error;
  /**
   * Only on table or on table and its children.
   * @var bool
   */
  protected $_only;

  /**
   * Constructor.
   * @param strayModelsDatabase $db associated database
   */
  public function __construct(strayModelsDatabase $db)
  {
    $this->_db = $db;
    $this->_args = array();
    $this->_only = false;
  }

  /**
   * Execute the constructed query.
   * @abstract
   * @return bool true if query has been well executed
   */
  abstract public function Execute();

  /**
   * Get value for argument $arg.
   * @param string $arg argument key
   * @return string $var value
   */
  public function GetArg($arg)
  {
    return $this->_args[$arg];
  }

  /**
   * Set value for argument $arg.
   * @param string $arg argument key
   * @param string $value argument value
   * @return strayModelsAQuery this
   */
  public function SetArg($arg, $value)
  {
    $this->_args[$arg] = $value;
    return $this;
  }

  /**
   * Check if $arg is set.
   * @param string $arg argument key
   * @return bool true if $arg exists
   */
  public function HasArg($arg)
  {
    return isset($this->_args[$arg]);
  }

  /**
   * Delete argument.
   * @param string $arg argument key
   * @return bool true if $arg existed
   */
  public function DelArg($arg)
  {
    if (false === $this->HasArg($arg))
      return false;
    unset($this->_args[$arg]);
    return true;
  }

  /**
   * Copy all the external query args except ones that are already set.
   * @param strayModelsAQuery $query external query
   */
  public function CopyArgs(strayModelsAQuery $query)
  {
    foreach ($query->_args as $key => $value)
      if (false === $this->HasArg($key))
        $this->SetArg($key, $value);
  }

  /**
   * Clear query.
   */
  public function Clear()
  {
    $this->_args = array();
  }

  /**
   * Get SQL error state.
   * @return string SQL state
   */
  public function GetErrorState()
  {
    return $this->_queryError[0];
  }

  /**
   * Get SQL error message.
   * @return string SQL message
   */
  public function GetErrorMessage()
  {
    return $this->_queryError[2];
  }

  /**
   * Get only status.
   * @return bool true if only is on
   */
  public function GetOnly()
  {
    return $this->_only;
  }

  /**
   * Set only status.
   * @param bool $value new only status
   */
  public function SetOnly($value)
  {
    if (true === is_bool($value))
      $this->_only = $value;
  }
}
