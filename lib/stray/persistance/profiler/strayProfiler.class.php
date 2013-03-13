<?php
/**
 * Singleton.
 * @brief Development profiler handle.
 * @author frederic.oudry@gmail.com
 */
class strayProfiler extends strayASingleton
{
  const PROFILER = 'PROFILER';
  const LEVEL_FW_DEBUG = 'LEVEL_FW_DEBUG';
  const LEVEL_SYS_NOTICE = 'LEVEL_SYS_NOTICE';
  const LEVEL_USER_NOTICE = 'LEVEL_USER_NOTICE';
  const LEVEL_DEBUG = 'LEVEL_DEBUG';
  const QUERY = 'QUERY';
  const TIMER = 'TIMER';
  const MAX = 10;

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
   * Log filename.
   * @var string
   */
  private $_logFilename;

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
   * Return array of data from the log file.
   * @param array log data
   */
  public function _GetArray()
  {
    return json_decode(file_get_contents($this->_GetFileName()), true);
  }

  /**
   * Return log filname.
   * @return string filename
   */
  private function _GetFileName()
  {
    return $this->_GetBaseDir() . $this->_logFilename;
  }

  /**
   * Return base dir where logs are saved.
   * @return string base dir path
   */
  private function _GetBaseDir()
  {
    return strayConfigInstall::fGetInstance()->GetConfigTmp() . 'profiler/';
  }

  /**
   * Called at request start.
   */
  public function RequestStart()
  {
    $date = date_create_from_format('U.u', microtime(true));
    if (false === $date)
      throw new strayExceptionError('strayProfiler : can\'t generate date');
    $this->_logFilename = $date->format('YmdHisu');
    $request = strayRoutingBootstrap::fGetInstance()->GetRequest();
    $this->AddProfilerLog('id', $this->_logFilename);
    $this->AddProfilerLog('php_version', phpversion());
    $this->AddProfilerLog('request_method', $request->GetMethod());
    $this->AddProfilerLog('request_is_ajax', (true === isset($_SERVER['HTTP_X_REQUESTED_WITH']) ?
      (false === empty($_SERVER['HTTP_X_REQUESTED_WITH']) || 'xmlhttprequest' === strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) ? true : false) : false));
    $this->AddProfilerLog('request_statusCode', http_response_code());
    $this->AddProfilerLog('request_headers', getallheaders());
    $this->AddProfilerLog('response_headers', headers_list());
    $this->AddProfilerLog('request_url', $request->GetUrl());
    $gets = array();
    foreach ($_GET as $getKey => $getValue)
      $gets[] = $getKey . ':' . var_export($getValue, true);
    $this->AddProfilerLog('request_get', $gets);
    $posts = array();
    foreach ($_POST as $postKey => $postValue)
      $posts[] = $postKey . ':' . var_export($postValue, true);
    $this->AddProfilerLog('request_post', $posts);
    $this->AddProfilerLog('fw_version', STRAY_VERSION);
    $this->AddProfilerLog('fw_version_code', STRAY_VERSION_CODE);
    $this->AddProfilerLog('fw_isDebug', $request->IsDebug());
    $this->AddProfilerLog('fw_plugins', (array)strayConfigInstall::fGetInstance()->GetConfig()['plugins']);
    $this->AddProfilerLog('app', $request->app);
    $this->AddProfilerLog('app_plugins', (array)strayConfigApp::fGetInstance($request->app)->GetConfig()['plugins']);
    $this->AddProfilerLog('page_widget', $request->widget);
    $this->AddProfilerLog('page_view', $request->view);
    $this->AddProfilerLog('page_params', $request->params);
    $this->AddProfilerLog('time_start', microtime(true));
  }

  /**
   * Called at request end. Call Render() if it has to.
   */
  public function RequestEnd()
  {
    $memoryMg = number_format(memory_get_usage() / 1024, 0, null, ' ');
    if (0 < count(straySession::fGetInstance()->All()))
      $this->AddProfilerLog('session_vars', straySession::fGetInstance()->All());
    // if plugin auth
    //$this->AddProfilerLog('user_isAuthenticated');
    $this->AddProfilerLog('memory_usage', $memoryMg);
    $this->AddProfilerLog('time_end', microtime(true));
    $this->AddProfilerLog('time_elapsed', $this->GetTimeEnd() - $this->GetTimeStart());
    $this->AddProfilerLog('name', $this->_logFilename);
    $this->AddProfilerLog('query_time', $this->_GetQueryTime());
    $this->AddProfilerLog('query_count',  $this->_GetQueryCount());
    file_put_contents($this->_GetBaseDir() . $this->_logFilename, json_encode($this->_logs));
    if (true === $this->needToDisplay)
      $this->Render();
  }

  /**
   * Render list of recent logs.
   */
  public function RenderLogList()
  {
  }

  /**
   * Render one log content.
   * @param string $name log name
   */
  public function RenderLog($name)
  {
    $this->_logFilename = $name;
    $this->Render('compact');
  }

  /**
   * Return last log data
   * @return string JSON data as string
   */
  public function GetLastLog()
  {
    $files = array_reverse($this->_GetLogFiles());
    if (0 < count($files)) 
    {
      $this->_logFilename = $files[0];
      return json_encode(array('id' => $this->_logFilename, 'html' => $this->_GetView('compact')));
    }
    return null;
  }

  /**
   * Get HTML code for specified template.
   * @param string $template template name
   * @return string generated HTML code
   */
  private function _GetView($template)
  {
    $env = strayExtTwig::fGetInstance()->GetEnvironment(STRAY_PATH_TO_LIB . 'stray/persistance/profiler/templates');
    return strayExtTwig::fGetInstance()->LoadTemplate($env, $template . '.html.twig', $this->_GetArray());
  }

  /**
   * Display the profiler bar.
   */
  public function Render($template = 'bar')
  {
    echo $this->_GetView($template);
  }

  /**
   * Add a profiler log.
   * @param string $msg log message
   * @param string $data log data
   * @param float $microtime log microtime
   */
  public function AddProfilerLog($msg, $data = null, $microtime = null)
  {
    $this->AddLog(self::PROFILER, $msg, $data, $microtime);
  }

  /**
   * Add a query log.
   * @param string $msg log message
   * @param string $query query
   * @param string $args query arguments
   * @param float $microtime query microtime
   */
  public function AddQueryLog($msg, $query, array $args = array(), $microtime = null)
  {
    $query = preg_replace('#\)#', ")\n", $query);
    $query = preg_replace('#\(#', "\n(", $query);
    $query = preg_replace('#;#', ";\n", $query);
    $query = preg_replace('#([A-Z] )([a-z])#', '${1}' . "\n" . ' ${2}', $query);
    $this->AddLog(self::QUERY, ($msg . ' #' . ($this->_GetQueryCount() + 1)), $query . "\n => WITH VALUES " . implode(', ', $args), $microtime);
  }

  /**
   * Add debug log, use it as well.
   * @param string $msg log message
   * @param string $data log data
   */
  public function AddDebugLog($msg, $data = null)
  {
    $this->AddLog(self::LEVEL_DEBUG, $msg, $data, microtime(true));
  }

  /**
   * Add routing timer.
   * @param float $microtime log microtime
   */
  public function AddTimerRoutingLog($microtime)
  {
    $this->AddTimerLog('routing', $microtime);
  }

  /**
   * Add view timer.
   * @param float $microtime log microtime
   */
  public function AddTimerViewLog($microtime)
  {
    $this->AddTimerLog('view', $microtime);
  }

  /**
   * Add render timer
   * @param float $microtime log microtime
   */
  public function AddTimerRenderLog($microtime)
  {
    $this->AddTimerLog('render', $microtime);
  }

  /**
   * Add custom timered log.
   * @param string $msg log message
   * @param float $microtime log microtime
   */
  public function AddTimerLog($msg, $microtime)
  {
    $this->AddLog(self::TIMER, $msg, null, $microtime);
  }

  /**
   * Add a new log. Called by strayLog methods.
   * @param enum $level log level
   * @param string $msg log content
   * @param string $data log data
   * @param float $microtime log microtime
   */
  public function AddLog($level, $msg, $data = null, $microtime = null)
  {
    $timestamp = (null == $microtime && self::PROFILER !== $level ? microtime(true) : $microtime);
    $data = (false === is_array($data) ? array($data) : $data);
    if(false === isset($this->_logs[$level]))
      $this->_logs[$level] = array();
    $this->_logs[$level][$msg] = array(
      'data' => $data,
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
    foreach ($this->_logs as $logLevel => $logs)
      if ($level === $logLevel)
        foreach ($logs as $message => $data)
          if ($msg === $message)
            return $data;
    return null;
  }

  /**
   * Get one log data by message and level.
   * @param string $msg log content
   * @param enum $level log level
   * @return string log data or null
   */
  public function GetLogData($message, $level = self::PROFILER)
  {
    $log = $this->GetLog($level, $message)['data'];
    return (true === is_array($log) ? current($log) : $log);
  }

  /**
   * Get all log data by message and level.
   * @param string $msg log content
   * @param enum $level log level
   * @return array log data
   */
  public function GetLogDataArray($message, $level = self::PROFILER)
  {
    return $this->GetLog($level, $message)['data'];
  }

  /**
   * Get logs. Can be filtered by level.
   * @param enum $level log level
   * @return array logs
   */
  public function GetLogs($level = null)
  {
    return (null === $level ? $this->_logs : (true === isset($this->_logs[$level]) ? $this->_logs[$level] : null));
  }

  /**
   * Get request start microtime.
   * @return float start microtime
   */
  public function GetTimeStart()
  {
    return $this->GetLogData('time_start');
  }

  /**
   * Get request end microtime.
   * @return float end microtime
   */
  public function GetTimeEnd()
  {
    return $this->GetLogData('time_end');
  }

  /**
   * Get request elapsed microtime.
   * @return float elapsed microtime
   */
  public function GetTimeElapsed()
  {
    return $this->GetLogData('time_elapsed');
  }

  /**
   * Return time spent for all queries.
   * @return float microtime
   */
  private function _GetQueryCount()
  {
    $logs = $this->GetLogs(self::QUERY);
    $total = 0;
    if (true === is_array($logs))
      foreach ($logs as $host => $queries)
        $total += count($queries['data']);
    return $total;
  }

  /**
   * Return time spent for all queries.
   * @return float microtime
   */
  private function _GetQueryTime()
  {
    $logs = $this->GetLogs(self::QUERY);
    $total = 0;
    if (true === is_array($logs))
      foreach ($logs as $log)
        $total += $log['timestamp'];
    return $total;
  }

  /**
   * Return non-hidden log filesnames.
   * @return array filenames
   */
  private function _GetLogFiles()
  {
    $files = scandir($this->_GetBaseDir());
    $out = array();
    foreach ($files as $file)
      if ('.' != $file[0])
        $out[] = $file;
    return $out;
  }

  /**
   * Delete all saved log files.
   */
  private function _DeleteOldLogs()
  {
    $files = $this->_GetLogFiles();
    if (self::MAX < count($files))
    {
      $filesToDelete = array_splice($files, 0, self::MAX);
      if (0 < count($filesToDelete))
        foreach ($filesToDelete as $fileToDelete)
          unlink($this->_GetBaseDir() . $fileToDelete);
    }
  }
}
