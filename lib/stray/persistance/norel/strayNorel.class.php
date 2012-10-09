<?php
/**
 * Singleton.
 * @brief NoREL connexion wrapper.
 * @author nekith@gmail.com
 */

final class strayNorel extends strayASingleton
{
  /**
   * NoREL connection object.
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
   * Config and create NoREL db object.
   * @param string $host NoREL server address
   * @param string $user NoREL server username
   * @param string $pass NoREL server password
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
   * Get a raw NoREL database.
   * Don't use this if you don't know what you are doing.
   * @param string $name database name
   * @return MongoDB NoREL database
   */
  public function GetDb($name)
  {
    return $this->_connection->selectDB($name);
  }
}
