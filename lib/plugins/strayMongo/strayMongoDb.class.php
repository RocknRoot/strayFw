<?php
/**
 * Multiton.
 * @brief MongoDB database wrapper.
 * @author nekith@gmail.com
 */

class strayMongoDb extends strayAMultiton
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
    if (true === isset($config['mongo'])
        && true === isset($config['mongo']['db_prefix']))
      $prefix = $config['mongo']['db_prefix'];
    else
    {
      strayLog::fGetInstance()->Notice('config.mongo.db_prefix isn\'t defined (set to "test_" by default)');
      $prefix = 'test_';
    }
    $this->_name = $prefix . $args[0];
  }

  /**
   * Get a new structure/collection.
   * @param string $struct structure/collection name
   * @return strayMongoQuery new query
   */
  public function __get($struct)
  {
    return new strayMongoQuery($this->_name, $struct);
  }

  /**
   * Insert data into database.
   * @param string $struct structure/collection name
   * @param array $data data to insert
   * @return bool true if $data wasn't empty
   */
  public function Insert($struct, array $data)
  {
    return strayMongo::fGetInstance()->GetDb($this->_name)->$struct->insert($data);
  }
}
