<?php
/**
 * @brief Databases' servers information container.
 * @author nekith@gmail.com
 */

class strayModelsServer
{
  /**
   * Link with database.
   * @var PDO
   */
  public $link;
  /**
   * Database server.
   * @var string
   */
  public $host;
  /**
   * Database server port.
   * @var int
   */
  public $port;
  /**
   * Database name.
   * @var string
   */
  public $name;
  /**
   * Database user.
   * @var string
   */
  public $user;
  /**
   * Database password.
   * @var string
   */
  public $pass;
  
  /**
   * toString
   * @return type string
   */
  public function __toString() {
    return $this->host . ' ' . $this->port . ' ' . $this->name;
  }
  
}
