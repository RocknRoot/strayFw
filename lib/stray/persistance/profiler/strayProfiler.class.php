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
    
    private $_timeStart;
    private $_timeEnd;
    private $_timeDiff;
    public $needToDisplay;
    
    /**
     * Logs strings.
     * @var array
     */
    private $_logs;
    
    protected function __construct()
    {
        $this->_logs = array();
        $this->needToDisplay = true;
        if (!file_exists($this->GetBaseDir()))
            mkdir($this->GetBaseDir());
        $this->DeleteOldLogs();
    }
    
    public function LoadProfiler($name)
    {
        if (true === file_exists($this->GetBaseFileName() . $name))
            $this->_logs = json_decode(file_get_contents($this->GetBaseFileName() . $name));
        return null;
    }
    
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
    
    public function RequestEnd()
    {
        $memoryMg =  number_format((memory_get_usage() / 1024), 0, '', ' ') ;
        $this->AddLog(self::LOG_PROFILER, 'memory_usage', $memoryMg);
        $this->AddLog(self::LOG_PROFILER, 'time_end', microtime());
        $this->AddLog(self::LOG_PROFILER, 'time_diff', $this->GetTimeEnd() - $this->GetTimeStart());
        $name = date_create_from_format('U.u', microtime(true))->format('Y-m-d:H:i:s.u');
        $this->AddLog(self::LOG_PROFILER, 'name', $name);
        file_put_contents($this->GetBaseFileName() . $name, json_encode($this->_logs));
        if (true === $this->needToDisplay)
            $this->Render();
    }
    
    public function Render()
    {
        $env = strayExtTwig::fGetInstance()->GetEnvironment(STRAY_PATH_TO_LIB . 'stray/persistance/profiler/templates');
        echo strayExtTwig::fGetInstance()->LoadTemplate($env, 'bar.html', array(
            'name' => $this->GetLogDescription('name'),
            'time_start' => $this->GormatNumber($this->GetTimeStart() * 1000),
            'time_end' => $this->GormatNumber($this->GetTimeEnd() * 1000),
            'time_diff' => $this->GormatNumber($this->GetTimeDiff() * 1000),
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
        
    private function DeleteOldLogs()
    {
        $files = array_reverse(scandir($this->GetBaseDir()));
        $filesToDelete = array();
        $count = 0;
        foreach($files as $file)
        {
            if('.' !== $file && '..' !== $file) 
            {
                $count++;
                if ($count >= self::LOG_MAX)
                    $filesToDelete[] = $file;
            }
        }
        if (count($filesToDelete) > 0) 
        {
            foreach ($filesToDelete as $fileToDelete)
                unlink($this->GetBaseDir() . $fileToDelete);
        }
    }
    
    private function GormatNumber($number) 
    {
        return number_format($number, 3, '.', ' ');
    }
    
    public function AddLog($level, $msg, $description = null, $microtime = null)
    {
        $timestamp = (null === $microtime && $level !== self::LOG_PROFILER) ? microtime() : $microtime;
        $description = (false === is_array($description)) ? array($description) : $description;
        
        $this->_logs[] = array(
            'level' => $level,
            'message' => $msg,
            'description' => $description,
            'timestamp' => $timestamp
        );
    }
    
    public function GetLog($level, $msg)
    {
        foreach ($this->_logs as $log)
        {
            if ($level === $log['level'] && $msg === $log['message'])
                return $log;
        }
        return null;
    }
    
    public function GetLogDescription($message, $level = self::LOG_PROFILER) 
    {
        return $this->GetLog($level, $message)['description'][0];
    }
    
    public function GetLogDescriptionArray($message, $level = self::LOG_PROFILER) 
    {
        return $this->GetLog($level, $message)['description'];
    }
    
    public function GetLogs($level = null)
    {
        if (null === $level) 
           $logs = $this->_logs;
        
        $logs =  array();
        foreach ($this->_logs as $log)
        {
            if ($level == $log['level'])
                $logs[] = $log;
        }
        return $logs;
    }
    
    public function GetTimeStart()
    {
        if (null === $this->_timeStart)
            $this->_timeStart = $this->GetLog(self::LOG_PROFILER, 'time_start')['description'][0];
        return $this->_timeStart;
    }
    
    public function GetTimeEnd()
    {
        if (null === $this->_timeEnd)
            $this->_timeEnd = $this->GetLog(self::LOG_PROFILER, 'time_end')['description'][0];
        return $this->_timeEnd;
    }
    
    public function GetTimeDiff()
    {
        if (null === $this->_timeDiff)
            $this->_timeDiff = $this->GetLog(self::LOG_PROFILER, 'time_diff')['description'][0];
        return $this->_timeDiff;
    }
    
    private function GetBaseFileName()
    {
        return $this->GetBaseDir();
    }
    
    private function GetBaseDir() 
    {
        return strayConfigInstall::fGetInstance()->GetConfigTmp() . 'profiler/';
    }
}
