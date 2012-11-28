<?php
/**
 * Singleton.
 * @brief MongoDB connexion wrapper.
 * @author nekith@gmail.com
 */

final class strayMongo extends strayASingleton
{
  /**
   * MongoDB connection object.
   * @var Mongo
   */
  private $_connection;

  /**
   * Constructor.
   */
  protected function __construct()
  {
    $this->_connection = null;
  }

  /**
   * Config and create Mongo db object.
   * @param string $host Mongo server address
   * @param string $user Mongo server username
   * @param string $pass Mongo server password
   */
  public function Config($host, $user, $pass)
  {
    if (null == $this->_connection)
    {
      if (null == $user)
        $this->_connection = new Mongo($host);
      else
        $this->_connection = new Mongo('mongodb://' . $user . ':' . $pass . '@' . $host);
    }
  }

  /**
   * Get a raw Mongo database.
   * Don't use this if you don't know what you are doing.
   * @param string $name database name
   * @return MongoDB Mongo database
   */
  public function GetDb($name)
  {
    return $this->_connection->selectDB($name);
  }
}
