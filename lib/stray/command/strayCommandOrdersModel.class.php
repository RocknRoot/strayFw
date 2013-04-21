<?php
/**
 * @brief These are all the models orders.
 * @author nekith@gmail.com
 * @final
 */

final class strayCommandOrdersModel
{
  /**
   * Create database.
   * @param array $params order parameters
   * @param array $options order options
   * @static
   */
  static public function fCreate(array $params = null,
      array $options = null)
  {
    if (1 != count($params))
      throw new strayException('wrong syntax for model:create');
    $config = strayConfigInstall::fGetInstance()->GetConfig();
    if (false === isset($config['databases'][$params[0]]))
      throw new strayExceptionFatal('can\'t find database "' . $params[0] . '" in settings');
    $db = $config['databases'][$params[0]];
    $db['alias'] = $params[0];
    $path = STRAY_PATH_TO_MODELS . $db['alias'];
    if (true === is_dir($path))
      throw new strayExceptionError('database "'. $db['alias'] . '" already exists');
    // dirs
    if (false === mkdir($path) || false === mkdir($path . '/classes')
        || false === mkdir($path . '/classes/base') || false === mkdir($path . '/migrations')
        || false === mkdir($path . '/i18n'))
      throw new strayExceptionFatal('can\'t mkdir');
    // files
    if (false === strayConfigFile::fCreate($path . '/schema')
        || false === strayConfigFile::fCreate($path . '/i18n/en'))
      throw new strayExceptionFatal('can\'t touch (this!)');
    // end
    echo 'Database "' . $db['alias'] . "\" created!\n";
  }

  /**
   * Generate models files.
   * @param array $params order parameters
   * @param array $options order options
   * @static
   */
  static public function fGenerate(array $params = null, array $options = null)
  {
    if (1 < count($params))
      throw new strayException('wrong syntax for models:generate');
    if (1 == count($params))
    {
      $path = STRAY_PATH_TO_MODELS . $params[0] . '/schema.' . strayConfigFile::EXTENSION;
      if (false === file_exists($path))
        throw new strayExceptionError('can\'t find file "' . $path . '"');
      self::_fGenerate($params[0]);
      echo 'Models for database "' . $params[0] . "\" have been generated!\n";
    }
    else
    {
      $handle = opendir(STRAY_PATH_TO_MODELS);
      if (false === $handle)
        throw new strayExceptionError('can\'t opendir models');
      while (false !== ($dir = readdir($handle)))
        if (true === is_dir(STRAY_PATH_TO_MODELS . $dir) && '.' != $dir[0]
            && true === file_exists(STRAY_PATH_TO_MODELS . $dir . '/schema.' . strayConfigFile::EXTENSION))
        {
          self::_fGenerate($dir);
          echo 'Models for database "' . $dir . "\" have been generated!\n";
        }
      closedir($handle);
    }
  }

  /**
   * Generate models files for database $name.
   * @param string $name database name/directory
   * @static
   */
  static private function _fGenerate($name)
  {
    $schema = strayConfigDatabase::fGetInstance($name)->Schema();
    if (null == $schema)
      throw new strayExceptionError('can\'t read/decode "' . STRAY_PATH_TO_MODELS
        . $name . '/schema.' . strayConfigFile::EXTENSION . '"');
    $require_file = fopen(STRAY_PATH_TO_MODELS . $name . '/classes/require.php', 'w+');
    if (false === fwrite($require_file, '<?php' . PHP_EOL))
      throw new strayExceptionError('can\'t write in "require.php"');
    foreach ($schema as $key => $elem)
    {
      // base class
      $constructor = null;
      $properties = null;
      $accessors = null;
      $allColumns = "  static public function fGetAllRealNameColumns()\n  {\n    return array(";
      $allColumnsAlias = "  static public function fGetAllAliasColumns()\n  {\n    return array(";
      foreach ($elem['columns'] as $colName => $column)
      {
        $properties .= '  protected $_column' . ucfirst($colName) . ";\n"
          . '  const COLUMN_' . strtoupper($colName) . ' = \'' . $elem['name'] . '.' . $column['name'] . "';\n";
        if ('enum' == $column['type'])
          foreach ($column['values'] as $v)
            $properties .= '  const ' . strtoupper($colName) . '_' . strtoupper($v) . ' = \'' . $v . '\';' . PHP_EOL;
        $constructor .= '    $this->_column' . ucfirst($colName)
          . " = array('name' => '" . $column['name']
          . '\', \'value\' => @$fetch[\'' . $column['name'] . '\']'
          . ', \'alias\' => \'' . $colName . "');\n";
        if (true === isset($column['primary']))
          $constructor .= '    $this->_primary[] = \'' . $colName . "';\n";
        // get
        $accessors .= '  public function Get' . ucfirst($colName) . "()\n  {\n";
        if ('string' == $column['type'] || 'char' == $column['type'])
          $accessors .= '    return stripslashes($this->_column' . ucfirst($colName) . "['value']);";
        elseif ('bool' == $column['type'])
          $accessors .= '    return filter_var($this->_column' . ucfirst($colName) . "['value'], FILTER_VALIDATE_BOOLEAN);";
        elseif ('json' == $column['type'])
          $accessors .= '    return json_decode($this->_column' . ucfirst($colName) . "['value'], true);";
        else
          $accessors .= '    return $this->_column' . ucfirst($colName) . "['value'];";
        $accessors .= "\n  }\n\n";
        // validate
        $accessors .= '  public function Validate' . ucfirst($colName)
          . '($value)' . "\n  {\n";
        if ('enum' == $column['type'])
        {
          $accessors .= '    if (false === in_array($value, array(\'' . implode('\', \'', (array)$column['values']) . '\')))' . PHP_EOL
            . '      return false;' . PHP_EOL;
        }
        $accessors .= "    return true;\n  }\n\n";
        // set
        $accessors .= '  public function Set' . ucfirst($colName) . '($value)' . "\n  {\n"
          . '    if (true === $this->Validate' . ucfirst($colName) . '($value))'
          . "\n    {\n";
        if ('bool' == $column['type'])
          $accessors .= '      if (1 == $value || true === $value)' . PHP_EOL . '        $this->_column'
            . ucfirst($colName) . '[\'value\'] = \'true\';' . PHP_EOL . '      else' . PHP_EOL
            . '        $this->_column' . ucfirst($colName) . '[\'value\'] = \'false\';' . PHP_EOL;
        elseif ('json' == $column['type'])
          $accessors .= '      $this->_column' . ucfirst($colName) . '[\'value\'] = json_encode($value);' . PHP_EOL;
        else
          $accessors .= '      $this->_column' . ucfirst($colName) . '[\'value\'] = $value;' . PHP_EOL;
        $accessors .= '      $this->_modified[\'' . $colName . "'] = true;\n"
          . '      return true;' . "\n    }\n"
          . '    return false;' . "\n  }\n\n";
        // reset
        $accessors .= '  public function Reset' . ucfirst($colName) . "()\n  {\n"
          . '    $this->_column' . ucfirst($colName) . "['value'] = null;\n  }\n\n";
        $allColumns .= "'" . $elem['name'] . '.' . $column['name'] . "', ";
        $allColumnsAlias .= "'" . $colName . "', ";
      }
      $allColumns = substr($allColumns, 0, -2) . ");\n  }\n\n";
      $allColumnsAlias = substr($allColumnsAlias, 0, -2) . ");\n  }\n\n";
      $path = STRAY_PATH_TO_MODELS . $name . '/classes/base/' . $key . '.model.php';
      $file = fopen($path, 'w+');
      if (false === fwrite($file, "<?php\n\n"
          . (true === isset($elem['inherits']) ?
              'require_once STRAY_PATH_TO_MODELS . \'' . $name . '/classes/base/' . $elem['inherits'] . ".model.php';\n" : null)
          . 'class models' . ucfirst($name) . 'Base' . ucfirst($key)
          . ' extends ' . (true === isset($elem['inherits']) ? 'models' . ucfirst($name) . 'Base' . ucfirst($elem['inherits']) : 'strayModelsATable')
          . "\n{\n" . $properties
          . "\n  static public function fGetDb()\n  {\n"
          . '    return strayModelsDatabase::fGetInstance(\'' . $name
          . "');\n  }\n\n  public function __construct("
          . 'array $fetch = null' . ")\n  {\n"
          . "    parent::__construct();\n    if (null != " . '$fetch' . ")\n"
          . '      $this->_new = false;' . "\n    else\n"
          . '      $fetch = array();'
          . "\n" . $constructor . "  }\n\n"
          . '  static public function fGetName()' . "\n  {\n    return '"
          . $elem['name'] . "';\n  }\n\n"
          . $accessors . $allColumns . $allColumnsAlias
          . "}\n"))
        throw new strayExceptionError('can\'t write in "' . $path . '"');
      fclose($file);
      // user class
      $path = STRAY_PATH_TO_MODELS . $name . '/classes/' . $key . '.model.php';
      if (false === file_exists($path))
      {
        $file = fopen($path, 'w+');
        if (false === fwrite($file, "<?php\n\nrequire_once STRAY_PATH_TO_MODELS . '"
            . $name . "/classes/base/" . $key . ".model.php';\n\nclass models" . ucfirst($name) . ucfirst($key)
            . ' extends models' . ucfirst($name) . 'Base' . ucfirst($key) . "\n{\n}\n"))
          throw new strayExceptionError('can\'t write in "' . $path . '"');
        fclose($file);
      }
      // require file
      if (false === fwrite($require_file, 'require \'' . $key . '.model.php\';' . PHP_EOL))
        throw new strayExceptionError('can\'t write in "require.php"');
    }
    fclose($require_file);
  }

  /**
   * Private constructor.
   */
  private function __construct() {}
}
