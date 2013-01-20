<?php
/**
 * @brief Abstract class for all SQL tables.
 * @abstract
 * @author nekith@gmail.com
 */
abstract class strayModelsATable
{
  /**
   * False if instance has been created with a select query result.
   * @var bool
   */
  protected $_new = true;
  /**
   * Names of primary rows.
   * @var string array
   */
  protected $_primary;
  /**
   * Names of modified columns.
   * @var array
   */
  protected $_modified;
  /**
   * Validate errors;
   * @var array
   */
  protected $_errors;
  /**
   * Flag for deletion.
   * @var bool
   */
  protected $_flagDelete;

  /**
   * Constructor.
   */
  public function __construct()
  {
    $this->_modified = array();
    $this->_errors = array();
    $this->_flagDelete = false;
  }

  /**
   * Save this table instance. Delete if _flagDelete is true.
   * @return bool true if saving went well
   */
  public function Save()
  {
    $startTime = microtime();
    if (false === $this->_new)
    {
      if (true === $this->_flagDelete)
        return $this->Delete();
      if (0 >= count($this->_modified))
        return false;
      $where = array();
      $set = array();
      $update = static::fGetDb()->QueryUpdate()->Update(static::fGetName());
      foreach ($this->_primary as $elem)
      {
        $row = $this->{'_column' . ucfirst($elem)};
        $where[] = $row['name'] . ' = :primary_' . $row['name'];
        $update->SetArg('primary_' . $row['name'], $row['value']);
      }
      $update->Where(implode(' AND ', $where));
      $vars = get_object_vars($this);
      foreach ($vars as $key => $elem)
        if (false !== strpos($key, '_column'))
        {
          $name = lcfirst(str_replace('_column', null, $key));
          if (true === array_key_exists($name, $this->_modified) && true === $this->_modified[$name])
          {
            $set[] = $elem['name'] . ' = :' . $elem['name'];
            $update->SetArg($elem['name'], $elem['value']);
          }
        }
      $update->Set(implode(', ', $set));
      $this->_modified = array();
      return $update->Execute();
    }
    else
    {
      if (true === $this->_flagDelete)
        return false;
      $names = null;
      $values = null;
      $arr = array();
      $vars = get_object_vars($this);
      foreach ($vars as $key => $elem)
        if (false !== strpos($key, '_column'))
        {
          if (true === isset($elem['value']))
          {
            $a = $this->$key;
            $names .= $a['name'] . ', ';
            $values .= ':' . $key . ', ';
            $arr[$key] = $elem['value'];
          }
        }
      $names = substr($names, 0, -2);
      $values = substr($values, 0, -2);
      $primaries = null;
      foreach ($this->_primary as $p)
      {
        $row = $this->{'_column' . ucfirst($p)};
        $primaries .= $row['name'] . ', ';
      }
      $primaries = substr($primaries, 0, -2);

      $statement = 'INSERT INTO ' . static::fGetName();
      if (0 != strlen($values))
        $statement .= ' (' . $names . ') VALUES(' . $values . ')';
      else
        $statement .= ' DEFAULT VALUES';
      $statement .= ' RETURNING ' . $primaries . ';';
      $query = static::fGetDb()->GetLink()->prepare($statement);
      $ret = $query->execute($arr);
      if (false === $ret)
      {
        $log = strayLog::fGetInstance();
        $info = $query->errorInfo();
        $log->Error('sql error : ' . $info[2] . ' (' . $statement . ')');
      }
      else
        $this->_new = false;
      $primaries = $query->fetch(PDO::FETCH_ASSOC);
      $max = count($this->_primary);
      for ($i = 0; $i < $max; ++$i)
      {
        $row = &$this->{'_column' . ucfirst($this->_primary[$i])};
        $row['value'] = $primaries[$row['name']];
      }
      if ('development' === STRAY_ENV)
        strayProfiler::fGetInstance()->AddQueryLog($this->fGetDb()->GetAlias() . implode(',', $this->fGetDb()->GetServers()), $query->queryString, $arr, microtime() - $startTime);  
      return $ret;
    }
  }

  /**
   * Delete an entry if primary keys are filled.
   * @return bool true if deleted
   */ 
  public function Delete()
  {
    $delete = static::fGetDb()->QueryDelete()->From(static::fGetName());
    foreach ($this->_primary as $elem)
    {
      $row = $this->{'_column' . ucfirst($elem)};
      if (false === isset($row['value']))
        return false;
      $delete->Where($row['name'] . ' = :' . $row['name']);
      $delete->SetArg($row['name'], $row['value']);
    }
    return $delete->Execute();
  }

  /**
   * Set flag for deletion. Gonna be delete when Save() is called.
   * @param bool $flag new flag value
   */
  public function FlagForDeletion($flag = true)
  {
    $this->_flagDelete = $flag;
  }

  /**
   * Get the table name.
   * @abstract
   * @return string table name
   */
  static public function fGetName()
  {
    throw new strayExceptionNotImplemented(__CLASS__ . '::' . __METHOD__);
  }

  /**
   * Get the database adapter instance.
   * @return strayModelsDatabase database adapter
   */
  static public function fGetDb()
  {
    throw new strayExceptionNotImplemented(__CLASS__ . '::' . __METHOD__);
  }

  /**
   * Replace columns real names by their alias names.
   * @param array $data SQL data with real names
   * @return array SQL data
   */
  public function WrapNames(array $data)
  {
    $columns = array();
    $vars = get_object_vars($this);
    foreach ($vars as $key => $elem)
      if (false !== strpos($key, '_column'))
        $columns[] = $elem;
    $newData = array();
    foreach ($data as $key => $elem)
      foreach ($columns as $col)
        if ($key == $col['name'])
        {
          $newData[$col['alias']] = $elem;
          break;
        }
    return $newData;
  }

  /**
   * Get errors list.
   * @return array
   */
  public function GetErrors()
  {
    return $this->_errors;
  }

  /**
   * Convert SQL data to an array of model-table objects.
   * @param array $data SQL data
   * @return array strayModelsATable objects
   */
  static public function fDataToObjects(array $data)
  {
    $objects = array();
    foreach ($data as $e)
      $objects[] = new static($e);
    return $objects;
  }

  /**
   * Fetch one entry satisfying all the specified conditions.
   * @param array $conditions where conditions
   * @return static model instance
   */
  static public function fFetch(array $conditions)
  {
    $select = static::fGetDb()->QuerySelect()->From(static::fGetName());
    $select->Select(static::fGetAllRealNameColumns())
      ->Limit(1);
    $i = 0;
    foreach ($conditions as $k => $v)
    {
      $select->Where($k . ' = :cond_' . $i);
      $select->SetArg('cond_' . $i, $v);
      ++$i;
    }
    $select->Execute();
    $data = $select->Fetch();
    if (0 == count($data))
      return false;
    return new static($data);
  }

  /**
   * Fetch all entries satisfying all the specified conditions.
   * @param array $conditions where conditions
   * @param string $order order
   * @return array tab of static model instances
   */
  static public function fFetchAll(array $conditions = array(), $order = null)
  {
    $select = static::fGetDb()->QuerySelect()->From(static::fGetName());
    $select->Select(static::fGetAllRealNameColumns());
    $i = 0;
    foreach ($conditions as $k => $v)
    {
      $select->Where($k . ' = :cond_' . $i);
      $select->SetArg('cond_' . $i, $v);
      ++$i;
    }
    if (null != $order)
      $select->OrderBy($order);
    $select->Execute();
    $raw = $select->FetchAll();
    if (0 == count($raw))
      return false;
    $data = array();
    foreach ($raw as $obj)
      $data[] = new static($obj);
    return $data;
  }

  /**
   * Count entries satisfying all the specified conditions.
   * @param array $conditions where conditions
   * @return int count result
   */
  static public function fCount(array $conditions)
  {
    $select = static::fGetDb()->QuerySelect()->From(static::fGetName());
    $select->Select(array('count' => 'COUNT(*)'));
    $i = 0;
    foreach ($conditions as $k => $v)
    {
      $select->Where($k . ' = :cond_' . $i);
      $select->SetArg('cond_' . $i, $v);
      ++$i;
    }
    $select->Execute();
    return $select->Fetch()['count'];
  }

  /**
   * Create a new select query.
   * @param bool $critical if true, will be executed on write server
   * @return strayModelsQuerySelect new query object
   */
  static public function fQuerySelect($critical = false)
  {
    return static::fGetDb()->QuerySelect()->From(static::fGetName());
  }

  /**
   * Create a new update query.
   * @return strayModelsQueryUpdate new query object
   */
  static public function fQueryUpdate()
  {
    return static::fGetDb()->QueryUpdate()->From(static::fGetName());
  }

  /**
   * Create a new delete query.
   * @return strayModelsQueryDelete new query object
   */
  static public function fQueryDelete()
  {
    return static::fGetDb()->QueryDelete()->From(static::fGetName());
  }

  /**
   * Get all columns real names.
   * @return array columns names
   */
  static public function fGetAllRealNameColumns()
  {
    throw new strayExceptionNotImplemented(__CLASS__ . '::' . __METHOD__);
  }

  /**
   * Get all columns aliases.
   * @return array columns names
   */
  static public function fGetAllAliasColumns()
  {
    throw new strayExceptionNotImplemented(__CLASS__ . '::' . __METHOD__);
  }
}
