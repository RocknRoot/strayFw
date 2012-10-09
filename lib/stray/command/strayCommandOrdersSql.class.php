<?php
/**
 * @brief These are all the SQL orders.
 * @author nekith@gmail.com
 * @final
 */

final class strayCommandOrdersSql
{
  /**
   * Build the SQL data.
   * @param array $params order parameters
   * @param array $options order options
   * @static
   */
  static public function fBuild(array $params = null, array $options = null)
  {
    if (1 < count($params))
      throw new strayException('wrong syntax for models:build');
    if (1 == count($params))
    {
      if (null == strayConfigDatabase::fGetInstance($params[0])->Schema())
        throw new strayExceptionError('can\'t find db "' . $params[0] . '"');
      self::_fBuild($params[0]);
    }
    else
    {
      $handle = opendir(STRAY_PATH_TO_MODELS);
      if (false === $handle)
        throw new strayExceptionError('can\'t opendir models');
      while (true == ($dir = readdir($handle)))
        if (true === is_dir(STRAY_PATH_TO_MODELS . $dir) && '.' != $dir[0]
            && null != strayConfigDatabase::fGetInstance($dir)->Schema())
        {
          self::_fBuild($dir);
        }
      closedir($handle);
    }
  }

  /**
   * Build the database $name SQL data.
   * @param string $name database name
   * @static
   */
  static private function _fBuild($name)
  {
    if (false === strayfCommandAskConfirm('Are you sure to delete all existing tables and data in "' . $name . '" ?'))
      return;
    $schema = strayConfigDatabase::fGetInstance($name)->Schema();
    if (null == $schema)
      throw new strayExceptionError('can\'t read/decode "' . STRAY_PATH_TO_MODELS
          . $name . '/schema.' . strayConfigFile::EXTENSION . '"');
    $db = strayModelsDatabase::fGetInstance($name);
    if (null == $db)
      throw new strayExceptionFatal('can\'t get database object for ' . $name);
    $info = strayConfigDatabase::fGetInstance($name)->Info();
    $info->last_up = date(strayModelsAMigration::DATE_FORMAT);
    strayConfigDatabase::fGetInstance($name)->Info($info);
    foreach ($schema as $key => $elem)
    {
      // drop foreign keys
      if (true === isset($elem->foreign))
      {
        foreach ($elem->foreign as $key => $null)
        {
          $sql = strayfModRemoveForeignKey($elem->name, $key);
          $ret = $db->Execute($sql);
          if (true !== $ret)
            echo 'sql:build | drop foreign keys | SQL notice : ' . $ret . ' (' . $sql . ')' . PHP_EOL;
        }
      }
    }
    foreach ($schema as $key => $elem)
    {
      // drop table
      $sql = strayfModRemoveTable($elem->name);
      $ret = $db->Execute($sql);
      if (true !== $ret)
        echo 'sql:build | drop table | SQL notice : ' . $ret . ' (' . $sql . ')' . PHP_EOL;
      // create types
      foreach ($elem->columns as $col)
      {
        $sql = strayfModRemoveType($col);
        if (false === empty($sql))
        {
          $ret = $db->Execute($sql);
          if (true !== $ret)
            echo 'sql:build | create type | SQL notice : ' . $ret . ' (' . $sql . ')' . PHP_EOL;
          $sql = strayfModCreateType($col);
          $ret = $db->Execute($sql);
          if (true !== $ret)
            echo 'sql:build SQL error : ' . $ret . ' (' . $sql . ')' . PHP_EOL;
        }
      }
      // create table
      $sql = strayfModCreateTable($elem, $schema);
      if (false === empty($sql))
      {
        $ret = $db->Execute($sql);
        if (true !== $ret)
          echo 'sql:build SQL error : ' . $ret . ' (' . $sql . ')' . PHP_EOL;
      }
      else
        echo 'sql:build SQL error : modCreateTable empty query' . PHP_EOL;
      // create indexes
      foreach ($elem->columns as $col)
      {
        $sql = strayfModCreateIndexes($col);
        if (false === empty($sql))
        {
          $ret = $db->Execute($sql);
          if (true !== $ret)
            echo 'sql:build SQL error : ' . $ret . ' (' . $sql . ')' . PHP_EOL;
        }
      }
    }
    // create foreign
    foreach ($schema as $tkey => $elem)
    {
      if (false === isset($elem->foreign))
      {
        echo 'No foreign key for table "' . $tkey . '"' . PHP_EOL;
        continue;
      }
      $sql = strayfModCreateForeignKeys($elem, $schema);
      $ret = $db->Execute($sql);
      if (true !== $ret)
        echo 'sql:build SQL error : ' . $ret . ' (' . $sql . ')' . PHP_EOL;
    }
    echo 'SQL tables for database "' . $name . '" have been created!' . PHP_EOL;
  }

  /**
   * Private constructor.
   */
  private function __construct() {}
}
