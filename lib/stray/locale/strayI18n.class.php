<?php
/**
 * @brief Internaliziation main class.
 * @author nekith@gmail.com
 */

final class strayI18n extends strayASingleton
{
  /**
   * List of known languages.
   * @var array
   */
  private $_languages;
  /**
   * Current language.
   * @var string
   */
  private $_current;
  /**
   * Translations.
   * @var array
   */
  private $_trads;

  /**
   * Constructor.
   */
  protected function __construct()
  {
    $this->_trads = array();
    //fetch languages
    if (true === class_exists('strayRoutingBootstrap'))
    {
      $app = strayRoutingBootstrap::fGetInstance()->GetRequest()->app;
      $dir = opendir(STRAY_PATH_TO_APPS . $app . '/i18n');
      if (false !== $dir)
      {
        while (false !== ($file = readdir($dir)))
          if (false === is_dir($file) && false != strrchr($file, '.' . strayConfigFile::EXTENSION))
            $this->_languages[] = strstr($file, '.' . strayConfigFile::EXTENSION, true);
        //get default language
        $config = strayConfigInstall::fGetInstance()->GetConfig();
        $lang = straySession::fGetInstance()->_strayI18nLanguage;
        if (null == $lang || false === $this->SetLanguage($lang))
        {
          if (null == $config['i18n'])
            throw new strayExceptionFatal('strayI18n: no install config');
          $host = strayRouting::fGetInstance()->GetHost();
          if (null != $host && true === isset($config['i18n']['hosts']))
          {
            $hosts = $config['i18n']['hosts'];
            foreach ($hosts as $h)
              if (false !== stripos($host, $h))
                if (true === $this->SetLanguage($h))
                  break;
          }
          if (null == $this->GetLanguage())
          {
            strayLog::fGetInstance()->Notice('strayI18n: no lang by host taken');
            if (null == $config['i18n']['default'])
              throw new strayExceptionFatal('strayI18n: no default lang');
            if (false === $this->SetLanguage($config['i18n']['default']))
              throw new strayExceptionFatal('strayI18n: can\'t set default lang');
          }
        }
        //get language content
        $this->GetContent();
      }
    }
    else
    {
      // fetch languages
      $dir = opendir(STRAY_PATH_TO_MODELS);
      while (false !== ($base = readdir($dir)))
      {
        if (true === is_dir(STRAY_PATH_TO_MODELS . $base) && false === stripos($base, '.'))
        {
          $subdir = opendir(STRAY_PATH_TO_MODELS . $base . '/i18n/');
          while (false != ($file = readdir($subdir)))
          {
            if (true === is_file(STRAY_PATH_TO_MODELS . $base . '/i18n/' . $file) && false !== stripos($file, '.' . strayConfigFile::EXTENSION))
              $this->_languages[] = strstr($file, '.' . strayConfigFile::EXTENSION, true);
          }
          closedir($subdir);
          break;
        }
      }
      closedir($dir);
      $this->_current = $this->_languages[0];
      $this->_GetContentModels();
    }
  }

  /**
   * Get current language content (trads).
   */
  public function GetContent()
  {
    $app = strayRoutingBootstrap::fGetInstance()->GetRequest()->app;
    $data = strayConfigFile::fParse(STRAY_PATH_TO_APPS . $app . '/i18n/' . $this->_current);
    if (false != $data)
      $this->_AddTrads($data);
    $this->_GetContentModels();
  }

  /**
   * Get current language models content (trads).
   */
  protected function _GetContentModels()
  {
    if (true === class_exists('strayRoutingBootstrap'))
    {
      $config = strayConfigApp::fGetInstance(strayRoutingBootstrap::fGetInstance()->GetRequest()->app);
      if (true === isset($config->GetConfig()['databases']))
      {
        foreach ($config->GetConfig()['databases'] as $db)
        {
          if (true === is_dir(STRAY_PATH_TO_MODELS . $db) && false === stripos($db, '.'))
          {
            $data = strayConfigFile::fParse(STRAY_PATH_TO_MODELS . $db . '/i18n/' . $this->_current);
            if (false != $data)
              $this->_AddTrads($data);
          }
        }
      }
    }
  }

  /**
   * Add trads to the fetched ones.
   * @param stdClass $data new trads
   */
  protected function _AddTrads(stdClass $data)
  {
    foreach ($data as $k => $e)
      $this->_trads[$k] = $e;
  }

  /**
   * Set a new language if it's in the languages list.
   * @param string $name new language name
   * @return bool true if new language is set
   */
  public function SetLanguage($name)
  {
    if (true === in_array($name, $this->_languages))
    {
      $this->_current = $name;
      if (true === class_exists('straySession'))
        straySession::fGetInstance()->_strayI18nLanguage = $name;
      return true;
    }
    return false;
  }

  /**
   * Get current language.
   * @return string current language
   */
  public function GetLanguage()
  {
    return $this->_current;
  }

  /**
   * Get a trad according to current language.
   * @param string $name trad name
   * @return string translation
   */
  public function __get($name)
  {
    if (false === isset($this->_trads[$name]))
    {
      strayLog::fGetInstance()->Error('can\'t find traduction for "' . $name . '"');
      return '(null)';
    }
    return $this->_trads[$name];
  }
}
