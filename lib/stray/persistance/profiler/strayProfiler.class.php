<?php
/**
 * Singleton.
 * @brief Development profiler handle.
 * @author frederic.oudry@gmail.com
 */

class strayProfiler extends strayASingleton
{
  const LOG_PROFILER = 01;
  const LOG_LEVEL_FW_DEBUG = 10;
  const LOG_LEVEL_SYS_NOTICE = 21;
  const LOG_LEVEL_USER_NOTICE = 31;
  const LOG_MAX = 50;

  /**
   * Microtime at request start.
   * @var int
   */
  private $_timeStart;
  /**
   * Microtime at request end.
   * @var int
   */
  private $_timeEnd;
  /**
   * Microtime length of request.
   * @var int
   */
  private $_timeDiff;
  /**
   * True if the profiler bar needs to be displayed.
   * @var bool
   */
  public $needToDisplay;
  /**
   * Logs strings.
   * @var array
   */
  private $_logs;

  /**
   * Construct.
   */
  protected function __construct()
  {
    $this->_logs = array();
    $this->needToDisplay = true;
    if (false === file_exists($this->_GetBaseDir()))
      mkdir($this->_GetBaseDir());
    $this->_DeleteOldLogs();
  }

  /**
   * Load an existing profile.
   * @param string $name profile name
   */
  public function LoadProfiler($name)
  {
    if (true === file_exists($this->_GetBaseFileName() . $name))
      $this->_logs = json_decode(file_get_contents($this->_GetBaseFileName() . $name));
    return null;
  }

  /**
   * Called at request start.
   */
  public function RequestStart()
  {
    $request = strayRoutingBootstrap::fGetInstance()->GetRequest();
    $this->AddLog(self::LOG_PROFILER, 'php_version', phpversion());
    $this->AddLog(self::LOG_PROFILER, 'request_method', $request->GetMethod());
    $this->AddLog(self::LOG_PROFILER, 'request_statusCode', http_response_code());
    $this->AddLog(self::LOG_PROFILER, 'request_headers', apache_request_headers());
    $this->AddLog(self::LOG_PROFILER, 'response_headers', apache_response_headers());
    $this->AddLog(self::LOG_PROFILER, 'request_url', $request->GetUrl());
    $gets = array();
    foreach ($_GET as $getKey => $getValue)
      $gets[] = $getKey . ':' . $getValue;
    $this->AddLog(self::LOG_PROFILER, 'request_get', $gets);
    $posts = array();
    foreach ($_POST as $postKey => $postValue)
      $posts[] = $postKey . ':' . $postValue;
    $this->AddLog(self::LOG_PROFILER, 'request_post', $posts);
    $this->AddLog(self::LOG_PROFILER, 'session_vars', var_export(straySession::fGetInstance()->All()));
    // if plugin auth
    //$this->AddLog(self::LOG_PROFILER, 'user_isAuthenticated');
    $this->AddLog(self::LOG_PROFILER, 'fw_version', STRAY_VERSION);
    $this->AddLog(self::LOG_PROFILER, 'fw_version_code', STRAY_VERSION_CODE);
    $this->AddLog(self::LOG_PROFILER, 'fw_isDebug', $request->IsDebug());
    $this->AddLog(self::LOG_PROFILER, 'fw_plugins', strayConfigInstall::fGetInstance()->GetConfig()['plugins']);
    $this->AddLog(self::LOG_PROFILER, 'app', $request->app);
    $this->AddLog(self::LOG_PROFILER, 'app_plugins', strayConfigApp::fGetInstance($request->app)->GetConfig()['plugins']);
    $this->AddLog(self::LOG_PROFILER, 'page_widget', $request->widget);
    $this->AddLog(self::LOG_PROFILER, 'page_view', $request->view);
    $this->AddLog(self::LOG_PROFILER, 'page_params', $request->params);
    $this->AddLog(self::LOG_PROFILER, 'time_start', microtime());
  }

  /**
   * Called at request end. Call Render() if it has to.
   */
  public function RequestEnd()
  {
    $memoryMg = number_format(memory_get_usage() / 1024, 0, null, ' ');
    $this->AddLog(self::LOG_PROFILER, 'memory_usage', $memoryMg);
    $this->AddLog(self::LOG_PROFILER, 'time_end', microtime());
    $this->AddLog(self::LOG_PROFILER, 'time_diff', $this->GetTimeEnd() - $this->GetTimeStart());
    $name = date_create_from_format('U.u', microtime(true))->format('Y-m-d:H:i:s.u');
    $this->AddLog(self::LOG_PROFILER, 'name', $name);
    file_put_contents($this->_GetBaseFileName() . $name, json_encode($this->_logs));
    if (true === $this->needToDisplay)
      $this->Render();
  }

  /**
   * Display the profiler bar.
   */
  public function Render()
  {
    $env = strayExtTwig::fGetInstance()->GetEnvironment(STRAY_PATH_TO_LIB . 'stray/persistance/profiler/templates');
    echo strayExtTwig::fGetInstance()->LoadTemplate($env, 'bar.html', array(
      'name' => $this->GetLogDescription('name'),
      'time_start' => $this->_FormatNumber($this->GetTimeStart() * 1000),
      'time_end' => $this->_FormatNumber($this->GetTimeEnd() * 1000),
      'time_diff' => $this->_FormatNumber($this->GetTimeDiff() * 1000),
      'memory_usage' => $this->GetLogDescription('memory_usage'),
      'fw' => array(
        'is_debug' => $this->GetLogDescription('fw_isDebug'),
        'version' => $this->GetLogDescription('fw_version'),
        'version_code' => $this->GetLogDescription('fw_version_code'),
      ),
      'app' => array(
        'name' => $this->GetLogDescription('app'),
        'plugins' => $this->GetLogDescription('app_plugins'),
      ),
      'page' => array(
        'widget' => $this->GetLogDescription('page_widget'),
        'view' => $this->GetLogDescription('page_view'),
        'params' => $this->GetLogDescriptionArray('page_params'),
      ),
      'php' => array(
        'version' => $this->GetLogDescriptionArray('php_version'),
      ),
      'request' => array(
        'method' => $this->GetLogDescription('request_method'),
        'status_code' => $this->GetLogDescription('request_statusCode'),
        'url' => $this->GetLogDescription('description'),
      ),
      'profiler_logs' => $this->GetLogs(self::LOG_PROFILER),
      'fw_logs' => $this->GetLogs(self::LOG_LEVEL_FW_DEBUG),
      'sys_logs' => $this->GetLogs(self::LOG_LEVEL_SYS_NOTICE),
      'user_logs' => $this->GetLogs(self::LOG_LEVEL_USER_NOTICE),
    ));
  }

  /**
   * Delete all saved log files.
   */
  private function _DeleteOldLogs()
  {
    $files = array_reverse(scandir($this->_GetBaseDir()));
    $filesToDelete = array();
    $count = 0;
    foreach ($files as $file)
      if ('.' !== $file && '..' !== $file)
      {
        ++$count;
        if (self::LOG_MAX <= $count)
          $filesToDelete[] = $file;
      }
    if (0 < count($filesToDelete))
      foreach ($filesToDelete as $fileToDelete)
        unlink($this->_GetBaseDir() . $fileToDelete);
  }

  /**
   * Format number for display matter.
   * @param int $number non-formated number
   * @return int formated number
   */
  private function _FormatNumber($number)
  {
    return number_format($number, 3, '.', ' ');
  }

  /**
   * Add a new log. Called by strayLog methods.
   * @param enum $level log level
   * @param string $msg log content
   * @param string $description log description
   * @param int $microtime log microtime
   */
  public function AddLog($level, $msg, $description = null, $microtime = null)
  {
    $timestamp = (null === $microtime && self::LOG_PROFILER !== $level ? microtime() : $microtime);
    $description = (false === is_array($description) ? array($description) : $description);
    $this->_logs[] = array(
      'level' => $level,
      'message' => $msg,
      'description' => $description,
      'timestamp' => $timestamp
    );
  }

  /**
   * Get a log by level and message.
   * @param enum $level log level
   * @param string $msg log content
   * @return array found log or null
   */
  public function GetLog($level, $msg)
  {
    foreach ($this->_logs as $log)
      if ($level === $log['level'] && $msg === $log['message'])
        return $log;
    return null;
  }

  /**
   * Get log description by message and level.
   * @param string $msg log content
   * @param enum $level log level
   * @return string log description or null
   */
  public function GetLogDescription($message, $level = self::LOG_PROFILER)
  {
    $log = $this->GetLog($level, $message)['description'];
    return (is_array($log)) ? current($log) : $log;
  }

  /**
   * Get all log descriptions by message and level.
   * @param string $msg log content
   * @param enum $level log level
   * @return array log descriptions
   */
  public function GetLogDescriptionArray($message, $level = self::LOG_PROFILER)
  {
    return $this->GetLog($level, $message)['description'];
  }

  /**
   * Get logs. Can be filtered by level.
   * @param enum $level log level
   * @return array logs
   */
  public function GetLogs($level = null)
  {
    if (null === $level)
      return $this->_logs;

    $logs = array();
    foreach ($this->_logs as $log)
      if ($level == $log['level'])
        $logs[] = $log;
    return $logs;
  }

  /**
   * Get request start microtime.
   * @return int start microtime
   */
  public function GetTimeStart()
  {
    if (null === $this->_timeStart)
      $this->_timeStart = current($this->GetLog(self::LOG_PROFILER, 'time_start')['description']);
    return $this->_timeStart;
  }

  /**
   * Get request end microtime.
   * @return int end microtime
   */
  public function GetTimeEnd()
  {
    if (null === $this->_timeEnd)
      $this->_timeEnd = current($this->GetLog(self::LOG_PROFILER, 'time_end')['description']);
    return $this->_timeEnd;
  }

  /**
   * Get request length microtime.
   * @return int length microtime
   */
  public function GetTimeDiff()
  {
    if (null === $this->_timeDiff)
      $this->_timeDiff = current($this->GetLog(self::LOG_PROFILER, 'time_diff')['description']);
    return $this->_timeDiff;
  }

  private function _GetBaseFileName()
  {
    return $this->_GetBaseDir();
  }

  private function _GetBaseDir()
  {
    return strayConfigInstall::fGetInstance()->GetConfigTmp() . 'profiler/';
  }
}