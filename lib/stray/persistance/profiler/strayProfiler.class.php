<?php

/**
 * Singleton.
 * @brief Development profiler handle.
 * @author frederic.oudry@gmail.com
 */
class strayProfiler extends strayASingleton {

  const LOG_PROFILER = 01;
  const LOG_LEVEL_FW_DEBUG = 10;
  const LOG_LEVEL_SYS_NOTICE = 21;
  const LOG_LEVEL_USER_NOTICE = 31;
  const LOG_QUERY = 41;
  const LOG_TIMER = 42;
  const LOG_MAX = 50;

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
    if (true === file_exists($this->_GetBaseDir() . $name))
      $this->_logs = json_decode(file_get_contents($this->_GetBaseDir() . $name));
    return null;
  }

  /**
   * Called at request start.
   */
  public function RequestStart()
  {
    $request = strayRoutingBootstrap::fGetInstance()->GetRequest();
    $this->AddProfilerLog('php_version', phpversion());
    $this->AddProfilerLog('request_method', $request->GetMethod());
    $this->AddProfilerLog('request_statusCode', http_response_code());
    $this->AddProfilerLog('request_headers', apache_request_headers());
    $this->AddProfilerLog('response_headers', apache_response_headers());
    $this->AddProfilerLog('request_url', $request->GetUrl());
    $gets = array();
    foreach ($_GET as $getKey => $getValue)
      $gets[] = $getKey . ':' . $getValue;
    $this->AddProfilerLog('request_get', $gets);
    $posts = array();
    foreach ($_POST as $postKey => $postValue)
      $posts[] = $postKey . ':' . $postValue;
    $this->AddProfilerLog('request_post', $posts);
    $this->AddProfilerLog('session_vars', var_export(straySession::fGetInstance()->All()));
    // if plugin auth
    //$this->AddProfilerLog('user_isAuthenticated');
    $this->AddProfilerLog('fw_version', STRAY_VERSION);
    $this->AddProfilerLog('fw_version_code', STRAY_VERSION_CODE);
    $this->AddProfilerLog('fw_isDebug', $request->IsDebug());
    $this->AddProfilerLog('fw_plugins', strayConfigInstall::fGetInstance()->GetConfig()['plugins']);
    $this->AddProfilerLog('app', $request->app);
    $this->AddProfilerLog('app_plugins', strayConfigApp::fGetInstance($request->app)->GetConfig()['plugins']);
    $this->AddProfilerLog('page_widget', $request->widget);
    $this->AddProfilerLog('page_view', $request->view);
    $this->AddProfilerLog('page_params', $request->params);
    $this->AddProfilerLog('time_start', microtime());
  }

  /**
   * Called at request end. Call Render() if it has to.
   */
  public function RequestEnd()
  {
    $memoryMg = number_format(memory_get_usage() / 1024, 0, null, ' ');
    $this->AddProfilerLog('memory_usage', $memoryMg);
    $this->AddProfilerLog('time_end', microtime());
    $this->AddProfilerLog('time_elapsed', $this->GetTimeEnd() - $this->GetTimeStart());
    $name = date_create_from_format('U.u', microtime(true))->format('Y-m-d:H:i:s.u');
    $this->AddProfilerLog('name', $name);
    file_put_contents($this->_GetBaseDir() . $name, json_encode($this->_logs));
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
        'timer' => array(
            'start' => $this->GetTimeStart(),
            'end' => $this->GetTimeEnd(),
            'elapsed' => $this->GetTimeElapsed(),
            'logs' => $this->GetLogs(self::LOG_TIMER)
        ),
        'query' => array(
            'time' => $this->_GetQueryTime(),
            'count' => count($this->GetLogs(self::LOG_QUERY)),
            'logs' => $this->GetLogs(self::LOG_QUERY)
        )
    ));
  }
  
  /**
   * Return base dir where logs are saved
   * @return string
   */
  private function _GetBaseDir()
  {
    return strayConfigInstall::fGetInstance()->GetConfigTmp() . 'profiler/';
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
      if ('.' !== $file && '..' !== $file) {
        ++$count;
        if (self::LOG_MAX <= $count)
          $filesToDelete[] = $file;
      }
    if (0 < count($filesToDelete))
      foreach ($filesToDelete as $fileToDelete)
        unlink($this->_GetBaseDir() . $fileToDelete);
  }

  /**
   * Shortcut to profiler log
   * @param type $msg
   * @param type $description
   * @param type $microtime
   */
  public function AddProfilerLog($msg, $description = null, $microtime = null)
  {
    $this->AddLog(self::LOG_PROFILER, $msg, $description, $microtime);
  }

  /**
   * Shortcut to query log
   * @param type $msg
   * @param type $query
   * @param type $args
   * @param type $microtime
   */
  public function AddQueryLog($msg, $query, $args = array(), $microtime = null)
  {
    $this->AddLog(self::LOG_QUERY, $msg, $query . ' with values' . implode(', ', $args), $microtime);
  }
  
  /**
   * Add routing timer
   * @param type $microtime
   */
  public function AddTimerRoutingLog($microtime)
  {
    $this->AddTimerLog('routing', $microtime);
  }
  
  /**
   * Add view timer
   * @param type $microtime
   */
  public function AddTimerViewLog($microtime)
  {
    $this->AddTimerLog('view', $microtime);
  }
  
  /**
   * Add render timer
   * @param type $microtime
   */
  public function AddTimerRenderLog($microtime)
  {
    $this->AddTimerLog('render', $microtime);
  }
  
  /**
   * Add timer (can be used by developper to add some timer logs)
   * @param type $msg
   * @param type $microtime
   */
  public function AddTimerLog($msg, $microtime)
  {
    $this->AddLog(self::LOG_TIMER, $msg, null, $microtime);
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
    return $this->GetLogDescription('time_start');
  }

  /**
   * Get request end microtime.
   * @return int end microtime
   */
  public function GetTimeEnd()
  {
    return $this->GetLogDescription('time_end');
  }

  /**
   * Get request length microtime.
   * @return int length microtime
   */
  public function GetTimeElapsed()
  {
    return $this->GetLogDescription('time_elapsed');
  }

  /**
   * Return time spent for all queries
   * @return @_FormatTime
   */
  private function _GetQueryTime()
  {
    $logs = $this->GetLogs(self::LOG_QUERY);
    $total = 0;
    if ($logs) {
      foreach ($logs as $log) {
        $total += $log['timestamp'];
      }
    }
    return $total;
  }
}