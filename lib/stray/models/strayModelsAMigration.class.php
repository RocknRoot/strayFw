<?php
/**
 * @brief Abstract class for migrations.
 * @abstract
 * @author nekith@gmail.com
 */
abstract class strayModelsAMigration
{
  /**
   * Date format for migrations.
   */
  const DATE_FORMAT = 'Y-m-d-H-i';

  /**
   * Database name.
   * @var string
   */
  protected $_db;

  /**
   * Migration dir.
   * @var string
   */
  protected $_dir;

  /**
   * Previous schema file decode result.
   * @var array
   */
  protected $_schema;

  /**
   * Next schema file decode result.
   * @var array
   */
  protected $_forwardSchema;

  /**
   * Registered mutations.
   * @var array
   */
  private $_mutations;

  /**
   * Constructor.
   * @param string $db database name
   * @param string $dir migration dir
   */
  public function __construct($db, $dir)
  {
    $this->_db = $db;
    $this->_dir = $dir;
    $this->_schema = null;
    $this->_forwardSchema = null;
    $this->_mutations = array();
    $this->Init();
  }

  /**
   * Get the associated database config.
   * @return strayConfigDatabase database config
   */
  public function GetDbConfig()
  {
    return strayConfigDatabase::fGetInstance($this->_db);
  }

  /**
   * Get the associated database instance.
   * @return strayModelsDatabase database instance
   */
  public function GetDb()
  {
    return strayModelsDatabase::fGetInstance($this->_db);
  }

  /**
   * Get next schema.
   * Current schema or next migration schema.
   * @return array forward schema
   */
  public function GetForwardSchema()
  {
    if (null == $this->_forwardSchema)
    {
      $path = $this->GetDbConfig()->Path() . '/migrations';
      $handle = opendir($path);
      if (false === $handle)
        throw new strayExceptionError('can\'t open "' . $path . '"');
      list($date_mine, $mine_path) = explode('_', $this->_dir, 2);
      $date_mine = self::fDateToTime($date_mine);
      $date_saved = null;
      $dir_saved = null;
      while (true == ($dir = readdir($handle)))
        if (true === is_dir($path . $dir) && '.' != $dir[0]
            && true === file_exists($path . $dir . '/Migration.class.php')
            && $this->GetDbConfig()->Path() != $path . $dir)
        {
          list($date, $name) = explode('_', $dir, 2);
          $date = self::fDateToTime($date);
          if ($date > $date_mine && $date > $date_saved)
          {
            $date_saved = $date;
            $dir_saved = $dir;
          }
        }
      closedir($handle);
      if (null == $dir_saved)
      {
        $this->_forwardSchema = $this->GetDbConfig()->Schema();
      }
      else
      {
        $this->_forwardSchema = strayConfigFile::fParse($path . $dir . '/schema');
      }
    }
    return $this->_forwardSchema;
  }

  /**
   * Get migration saved schema.
   * @return array previous schema
   */
  public function GetSchema()
  {
    if (null == $this->_schema)
    {
      $this->_schema = strayConfigFile::fParse($this->GetDbConfig()->Path() . '/migrations/' . $this->_dir . '/schema');
    }
    return $this->_schema;
  }

  /**
   * Register a new mutation.
   * @param strayModelsAMutation $mutation new mutation
   */
  public function AddMutation(strayModelsAMutation $mutation)
  {
    $mutation->SetMigration($this);
    $this->_mutations[] = $mutation;
  }

  /**
   * User-side method for setting up migration.
   */
  abstract public function Init();

  /**
   * User-side method for displaying migration help screen.
   */
  abstract public function Help();

  /**
   * Run the migration.
   * @return bool true if migration went well
   */
  public function Execute()
  {
    foreach ($this->_mutations as $mut)
      $mut->Execute();
  }

  /**
   * Rewind the migration.
   * @return bool true if rewinding went well
   */
  public function Rewind()
  {
    $mutations = array_reverse($this->_mutations);
    foreach ($mutations as $mut)
      $mut->Rewind();
  }

  /**
   * Transform migration date to UNIX time.
   * @param string $date migration date
   * @return int migration UNIX time
   */
  static public function fDateToTime($date)
  {
    $arr = explode('-', $date);
    return mktime($arr[3], $arr[4], 0, $arr[1], $arr[2], $arr[0]);
  }
}
