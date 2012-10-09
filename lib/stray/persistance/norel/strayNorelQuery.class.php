<?php
/**
 * @brief NoREL query wrapper.
 * @author nekith@gmail.com
 */

class strayNorelQuery
{
  /**
   * Norel database name.
   * @var string
   */
  private $_db;
  /**
   * Struct id.
   * @var string
   */
  private $_struct;

  /**
   * Constructor.
   * @param string $db database name
   * @param string $struct struct id
   */
  public function __construct($db, $struct)
  {
    $this->_db = $db;
    $this->_struct = $struct;
  }

  /**
   * Fetch one object with specified filters.
   * @param array $filters filters
   * @return array result object
   */
  public function Fetch(array $filters = null)
  {
    return strayNorel::fGetInstance()->GetDb($this->_db)->{$this->_struct}->findOne($filters);
  }

  /**
   * Feetch all objects with specified filters.
   * @param array $filters filters
   * @return array result object
   */
  public function FetchAll(array $filters = null)
  {
    return strayNorel::fGetInstance()->GetDb($this->_db)->{$this->_struct}->find($filters);
  }

  /**
   * Remove one object with specified filters.
   * @param array $filters filters
   * @return bool true if remove was succesful
   */
  public function RemoveOne(array $filters)
  {
    return strayNorel::fGetInstance()->GetDb($this->_db)->{$this->_struct}->remove($filters, array('justOne' => true));
  }

  /**
   * Remove all objects with specified filters.
   * @param array $filters filters
   * @return bool true if remove was succesful
   */
  public function Remove(array $filters)
  {
    return strayNorel::fGetInstance()->GetDb($this->_db)->{$this->_struct}->remove($filters);
  }
}
