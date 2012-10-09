<?php
/**
 * @brief These are all the migrations orders.
 * @author nekith@gmail.com
 * @final
 */

final class strayCommandOrdersMigration
{
  /**
   * Create migration.
   * @param array $params order parameters
   * @param array $options order options
   * @static
   */
  static public function fCreate(array $params = null, array $options = null)
  {
    if (2 != count($params))
      throw new strayException('wrong syntax for migration:create');
    $base_path = STRAY_PATH_TO_MODELS . $params[0];
    if (false === is_dir($base_path))
      throw new strayException('database "' . $params[0] . '" doesn\'t exist');
    $date = date(strayModelsAMigration::DATE_FORMAT);
    $path = $base_path . '/migrations/' . $date . '_' . ucfirst($params[1]);
    // dirs
    if (false === mkdir($path))
      throw new strayExceptionFatal('can\'t mkdir');
    // files
    if (false === touch($path . '/' . $params[1] . '.migration.php'))
      throw new strayExceptionFatal('can\'t touch (this!)');
    // Migration.class.php
    $file = fopen($path . '/' . $params[1] . '.migration.php', 'w+');
    if (false === fwrite($file, "<?php\nclass Migration" . ucfirst($params[1])
        . " extends strayModelsAMigration\n{\n  public function Init()"
        . "\n  {\n  }\n\n  public function Help()\n  {\n  }\n}\n"))
      throw new strayExceptionFatal('can\'t write in "' . $path . '/' . $params[1] . '.migration.php"');
    fclose($file);
    // copy schema.xxx
    if (false === copy($base_path . '/schema.' . strayConfigFile::EXTENSION, $path . '/schema.' . strayConfigFile::EXTENSION))
      throw new strayExceptionFatal('can\'t make a copy of schema.' . strayConfigFile::EXTENSION);
    // end
    echo 'Migration "' . $params[1] . '" for database "' . $params[0] . "\" has been created!\n";
  }

  /**
   * Display migration help.
   * @param array $params order parameters
   * @param array $options order options
   * @static
   */
  static public function fHelp(array $params = null, array $options = null)
  {
    if (2 != count($params))
      throw new strayException('wrong syntax for migration:help');
    $base_path = STRAY_PATH_TO_MODELS . $params[0] . '/migrations/';
    $handle = opendir($base_path);
    $migration_dir = null;
    if (false === $handle)
      throw new strayExceptionError('can\'t find database "' . $params[0] . '"');
    while (true == ($dir = readdir($handle)))
      if (true === is_dir($base_path . $dir) && '.' != $dir[0])
      {
        list($null, $migration_name) = explode('_', $dir, 2);
        if ($migration_name == ucfirst($params[1]))
        {
          $migration_dir = $dir;
          break;
        }
      }
    closedir($handle);
    if (null == $migration_dir)
      throw new strayExceptionError('can\'t find migration "' . $params[1] . '" for database "' . $params[0] . '"');
    echo 'Help screen for migration "' . ucfirst($params[1]) . "\"\n\n";
    require $base_path . $migration_dir . '/' . $params[1] . '.migration.php';
    $class = 'Migration' . ucfirst($params[1]);
    $migration = new $class($params[0]);
    $migration->Help();
    echo "\n";
  }

  /**
   * Execute migrations.
   * @param array $params order parameters
   * @param array $options order options
   * @static
   */
  static public function fMigrate(array $params = null, array $options = null)
  {
    if (1 < count($params))
      throw new strayException('wrong syntax for migration:migrate');
    if (1 == count($params))
    {
      $path = STRAY_PATH_TO_MODELS . $params[0] . '/migrations/';
      if (false === is_dir($path))
        throw new strayException('database "' . $params[0] . '" doesn\'t exist');
      echo 'Searching migrations for "' . $params[0] . '"...' . PHP_EOL;
      self::_fMigrate($params[0]);
      echo 'Done!' . PHP_EOL;
    }
    else
    {
      $handle = opendir(STRAY_PATH_TO_MODELS);
      if (false === $handle)
        throw new strayExceptionError('can\'t opendir models');
      while (true == ($dir = readdir($handle)))
        if (true === is_dir(STRAY_PATH_TO_MODELS . $dir) && '.' != $dir[0]
            && true === is_dir(STRAY_PATH_TO_MODELS . $dir . '/migrations'))
        {
          echo 'Searching migrations for "' . $dir . '"...' . PHP_EOL;
          self::_fMigrate($dir);
          echo 'Done!' . PHP_EOL;
        }
      closedir($handle);
    }
  }

  /**
   * Execute migrations for database $name.
   * @param string $name database name/directory
   * @static
   */
  static private function _fMigrate($db)
  {
    $info = strayConfigDatabase::fGetInstance($db)->Info();
    $date_old = strayModelsAMigration::fDateToTime($info->last_up);
    if ($date_old == 0)
      throw new strayExceptionError('do a sql:build before migrate');
    $date_new_str = date(strayModelsAMigration::DATE_FORMAT);
    $date_new = strayModelsAMigration::fDateToTime($date_new_str);
    $base_path = STRAY_PATH_TO_MODELS . $db . '/migrations/';
    $handle = opendir($base_path);
    if (false === $handle)
      throw new strayExceptionError('can\'t opendir "' . $base_path . '"');
    while (true == ($dir = readdir($handle)))
      if (true === is_dir($base_path . $dir) && '.' != $dir[0])
      {
        list($date_str, $migration_name) = explode('_', $dir, 2);
        $date = strayModelsAMigration::fDateToTime($date_str);
        if ($date_old < $date && $date <= $date_new)
        {
          echo 'Executing ' . $migration_name . ' migration (' . $date_str . ")...\n";
          require $base_path . $dir . '/' . strtolower($migration_name) . '.migration.php';
          $class = 'Migration' . $migration_name;
          $migration = new $class($db, $dir);
          $migration->Execute();
        }
      }
    closedir($handle);
    $info->last_up = $date_new_str;
    strayConfigDatabase::fGetInstance($db)->Info($info);
  }

  /**
   * Rewind a migration.
   * @param array $params order parameters
   * @param array $options order options
   * @static
   */
  static public function fRewind(array $params = null, array $options = null)
  {
    if (2 != count($params))
      throw new strayException('wrong syntax for migration:rewind');
    $base_path = STRAY_PATH_TO_MODELS . $params[0] . '/migrations/';
    $rewind_date = strayModelsAMigration::fDateToTime(date(strayModelsAMigration::DATE_FORMAT, 0));
    $rewind_dir = null;
    $last_date = strayModelsAMigration::fDateToTime(strayConfigDatabase::fGetInstance($params[0])->Info()->last_up);
    $handle = opendir($base_path);
    if (false === $handle)
      throw new strayExceptionError('can\'t opendir "' . $base_path . '"');
    while (true == ($dir = readdir($handle)))
      if (true === is_dir($base_path . $dir) && '.' != $dir[0])
      {
        list($date_str, $migration_name) = explode('_', $dir, 2);
        if ($migration_name == ucfirst($params[1]))
        {
          $rewind_date = strayModelsAMigration::fDateToTime($date_str);
          $rewind_dir = $dir;
          break;
        }
      }
    if ($rewind_date == strayModelsAMigration::fDateToTime(date(strayModelsAMigration::DATE_FORMAT, 0)))
      throw new strayExceptionFatal('Migration "' . $params[1] . '" can\'t be found');
    if ($rewind_date > $last_date)
      throw new strayExceptionFatal('Migration "' . $params[1] . '" hasn\'t been executed yet');
    rewinddir($handle);
    while (true == ($dir = readdir($handle)))
      if (true === is_dir($base_path . $dir) && '.' != $dir[0])
      {
        list($date, $migration_name) = explode('_', $dir, 2);
        $date = strayModelsAMigration::fDateToTime($date);
        if ($rewind_date > $date && $last_date <= $date)
          throw new strayExceptionFatal('Migration "' . $params[1] . '" isn\'t the last executed');
      }
    echo 'Rewinding ' . $migration_name . " migration...\n";
    require $base_path . $rewind_dir . '/' . $params[1] . '.migration.php';
    $class = 'Migration' . ucfirst($params[1]);
    $migration = new $class($params[0], $rewind_dir);
    $migration->Rewind();
    closedir($handle);
    $info->last_up = date(strayModelsAMigration::DATE_FORMAT, $rewind_date - 1);
    strayConfigDatabase::fGetInstance($params[0])->Info($info);
  }

  /**
   * Private constructor.
   */
  private function __construct() {}
}
