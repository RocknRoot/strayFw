<?php
/**
 * Singleton.
 * @brief Models' database class.
 * @author nekith@gmail.com
 */

class strayModelsDatabase extends strayAMultiton
{
  /**
   * Array of used servers.
   * @var array
   */
  private $_servers;
  /**
   * Database alias.
   * @var string
   */
  private $_alias;
  /**
   * Better than 0 if BeginTransaction has been called.
   * @var int
   */
  private $_isTransactionning;

  /**
   * Constructor.
   * @param array $args arguments
   */
  protected function __construct(array $args)
  {
    $this->_alias = $args[0];
    $config = strayConfigInstall::fGetInstance()->GetConfig()['databases'][$this->_alias];
    $this->_isTransactionning = 0;
    if (true === isset($config['read']))
    {
      $server = new strayModelsServer();
      $server->name = $config->read->name;
      $server->host = $config->read->server;
      $server->user = $config->read->user;
      $server->pass = $config->read->pass;
      $this->_servers['read'] = $server;
      $server = new strayModelsServer();
      $server->name = $config->write->name;
      $server->host = $config->write->server;
      $server->user = $config->write->user;
      $server->pass = $config->write->pass;
      $this->_servers['write'] = $server;
    }
    else if (true === isset($config['host']))
    {
      $server = new strayModelsServer();
      $server->name = $config['name'];
      $server->host = $config['host'];
      $server->port = $config['port'];
      $server->user = $config['user'];
      $server->pass = $config['pass'];
      $this->_servers['all'] = $server;
    }
    // tables
    require STRAY_PATH_TO_MODELS . $this->_alias . '/classes/require.php';
  }

  /**
   * Destructor.
   */
  public function __destruct()
  {
    $this->Disconnect();
  }

  /**
   * Connect to the database.
   */
  public function Connect()
  {
    if (false === $this->IsConnected())
    {
      try
      {
        if (true === isset($this->_servers['all']))
        {
          $this->_servers['all']->link = new PDO('pgsql:host=' . $this->_servers['all']->host . ';dbname='
                . $this->_servers['all']->name, $this->_servers['all']->user, $this->_servers['all']->pass);
        }
        else
        {
          $this->_servers['read']->link = new PDO('pgsql:host=' . $this->_servers['read']->host . ';dbname='
                . $this->_servers['read']->name, $this->_servers['read']->user, $this->_servers['read']->pass);
          $this->_servers['write']->link = new PDO('pgsql:host=' . $this->_servers['write']->host . ';dbname='
                . $this->_servers['write']->name, $this->_servers['write']->user, $this->_servers['write']->pass);
        }
      }
      catch (PDOException $e)
      {
        $this->Disconnect();
        strayLog::fGetInstance()->Error('can\'t connect to database (' . $e->getMessage() . ')');
        throw new strayExceptionFatal('can\'t connect to database (' . $e->getMessage() . ')');
      }
    }
  }

  /**
   * Disconnect link to database.
   */
  public function Disconnect()
  {
    if (true === isset($this->_servers['all']))
      unset($this->_servers['all']->link);
    else
    {
      unset($this->_servers['read']->link);
      unset($this->_servers['write']->link);
    }
  }

  /**
   * Check if connected to database.
   * @return bool true if link is valid
   */
  public function IsConnected()
  {
    if (true === isset($this->_servers['all']))
      return isset($this->_servers['all']->link);
    return isset($this->_servers['read']->link);
  }

  /**
   * Get the PDO object associated to database.
   * @param bool $read if true, return link to read server
   * @return PDO link to database
   */
  public function GetLink($read = true)
  {
    if (false === $this->IsConnected())
      $this->Connect();
    if (true === isset($this->_servers['all']))
      return $this->_servers['all']->link;
    if (true === $read && false === $this->_isTransactionning)
      return $this->_servers['read']->link;
    return $this->_servers['write']->link;
  }

  /**
   * Execute a SQL query code.
   * @param string $sql SQL code to execute
   * @param bool $select if true return fetchAll results
   * @return mixed : boolean(true) or array(results) if went well; string(error) if not
   */
  public function Execute($sql, $select = false)
  {
    if (false === $this->IsConnected())
      $this->Connect();
    if (false === $select)
      $link = $this->GetLink(false);
    else
      $link = $this->GetLink();
    $query = $link->prepare($sql);
    if ($query->execute() == false)
    {
      $errorInfo = $query->errorInfo();
      return $errorInfo[2];
    }
    if (true === $select)
      return $query->fetchAll();
    return true;
  }

  /**
   * Create a new select query.
   * @param bool $critical if true, will be executed on write server
   * @return strayModelsQuerySelect new query object
   */
  public function QuerySelect($critical = false)
  {
    return new strayModelsQuerySelect($this, $critical);
  }

  /**
   * Create a new update query.
   * @return strayModelsQueryUpdate new query object
   */
  public function QueryUpdate()
  {
    return new strayModelsQueryUpdate($this);
  }

  /**
   * Create a new delete query.
   * @return strayModelsQueryDelete new query object
   */
  public function QueryDelete()
  {
    return new strayModelsQueryDelete($this);
  }

  /**
   * Begin a SQL transaction.
   * @return bool true if successful
   */
  public function BeginTransaction()
  {
    if (false === $this->IsConnected())
      $this->Connect();
    ++$this->_isTransactionning;
    if (2 <= $this->_isTransactionning)
      return false;
    if (true === isset($this->_servers['all']))
      return $this->_servers['all']->link->beginTransaction();
    return $this->_servers['write']->link->beginTransaction();
  }

  /**
   * Commit transaction modifications.
   * @return bool true if successful
   */
  public function Commit()
  {
    if (false === $this->IsConnected())
      $this->Connect();
    --$this->_isTransactionning;
    if (0 != $this->_isTransactionning)
      return false;
    if (true === isset($this->_servers['all']))
      return $this->_servers['all']->link->commit();
    return $this->_servers['write']->link->commit();
  }

  /**
   * Roll back transaction modifications.
   * @return bool true if successful
   */
  public function RollBack()
  {
    if (false === $this->IsConnected())
      $this->Connect();
    --$this->_isTransactionning;
    if (0 != $this->_isTransactionning)
      return false;
    if (true === isset($this->_servers['all']))
      return $this->_servers['all']->link->rollBack();
    return $this->_servers['write']->link->rollBack();
  }

  /**
   * Return database alias.
   * @return string database alias
   */
  public function GetAlias() 
  {
    return $this->_alias;
  }
}
