<?php
/**
 * Multiton.
 * @brief NoREL database wrapper.
 * @author nekith@gmail.com
 */

class strayNorelDb extends strayAMultiton
{
  /**
   * Database name.
   * @var string
   */
  private $_name;

  /**
   * Construct with database $args[0].
   * @param array $args arguments
   */
  protected function __construct(array $args)
  {
    $config = strayConfigInstall::fGetInstance()->GetConfig();
    if (true === isset($config['norel'])
        && true === isset($config['norel']['db_prefix']))
      $prefix = $config['norel']['db_prefix'];
    else
    {
      strayLog::fGetInstance()->Notice('config:norel:db_prefix isn\'t defined (set to "test_" by default)');
      $prefix = 'test_';
    }
    $this->_name = $prefix . $args[0];
  }

  /**
   * Get a new structure/collection.
   * @param string $struct structure/collection name
   * @return strayNorelQuery new query
   */
  public function __get($struct)
  {
    return new strayNorelQuery($this->_name, $struct);
  }

  /**
   * Insert data into database.
   * @param string $struct structure/collection name
   * @param array $data data to insert
   * @return bool true if $data wasn't empty
   */
  public function Insert($struct, array $data)
  {
    return strayNorel::fGetInstance()->GetDb($this->_name)->$struct->insert($data);
  }
}
