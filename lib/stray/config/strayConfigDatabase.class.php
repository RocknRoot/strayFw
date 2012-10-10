<?php
/**
 * Multiton
 * @brief Configuration class for models.
 * @author nekith@gmail.com
 * @final
 */

final class strayConfigDatabase extends strayAMultiton
{
  /**
   * Database name.
   * @var string
   */
  private $_db;

  /**
   * Database info file decode result.
   * @var array
   */
  private $_info;

  /**
   * Database schema file decode result.
   * @var array
   */
  private $_schema;

  /**
   * Construct with database $args[0].
   * @param array $args arguments
   */
  protected function __construct($args)
  {
    $this->_db = $args[0];
    $this->_info = false;
    $this->_schema = false;
  }

  /**
   * Get/Set the database info file data.
   * @param array $info new info value
   * @return array info file decode result
   */
  public function Info(array $info = null)
  {
    if (null == $info)
    {
      if (false === $this->_info)
      {
        if (false === file_exists(STRAY_PATH_TO_MODELS . $this->_db . '/info.' . strayConfigFile::EXTENSION))
        {
          $object = array('last_up' => date('Y-m-d-H-i', 0));
          if (false === strayConfigFile::fCreate(STRAY_PATH_TO_MODELS . $this->_db . '/info', strayConfigFile::fEncode($object)))
          {
            $this->_info = null;
            strayLog::fGetInstance()->FwFatal('can\'t create "' . STRAY_PATH_TO_MODELS . $this->_db . '/info.' . strayConfigFile::EXTENSION . '"');
            return null;
          }
        }
        $this->_info = strayConfigFile::fParse(STRAY_PATH_TO_MODELS . $this->_db . '/info');
      }
    }
    else
    {
      $data = strayConfigFile::fEncode($info);
      if (null != $data)
        strayConfigFile::fCreate(STRAY_PATH_TO_MODELS . $this->_db . '/info', $data);
      $this->_info = $info;
    }
    return $this->_info;
  }

  /**
   * Get the database directory path.
   * @return string databse path
   */
  public function Path()
  {
    return STRAY_PATH_TO_MODELS . $this->_db;
  }

  /**
   * Get the database schema file data.
   * @return array schema file decode result
   */
  public function Schema()
  {
    if (false === $this->_schema)
      $this->_schema = strayConfigFile::fParse(STRAY_PATH_TO_MODELS . $this->_db . '/schema');
    return $this->_schema;
  }
}
