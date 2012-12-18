<?php
/**
 * Singleton.
 * @brief Logging class.
 * @author nekith@gmail.com
 */

final class strayLog extends strayASingleton
{
  protected function __construct() {}

  protected function _Write($msg)
  {
    error_log($msg);
  }

  public function SysError($msg)
  {
    $this->_Write('[Sys|Error] ' . $msg);
    if (true === strayRoutingBootstrap::fGetInstance()->GetRequest()->IsDebug())
      throw new strayExceptionError('[Sys|Error] ' . $msg);
  }

  public function SysWarning($msg)
  {
    $this->_Write('[Sys|Warning] ' . $msg);
    if (true === strayRoutingBootstrap::fGetInstance()->GetRequest()->IsDebug())
      throw new strayExceptionError('[Sys|Warnin] ' . $msg);
  }

  public function SysNotice($msg)
  {
    $this->_Write('[Sys|Notice] ' . $msg);
  }

  public function FwFatal($msg)
  {
    $this->_Write('[Fw|Fatal] ' . $msg);
    if (true === strayRoutingBootstrap::fGetInstance()->GetRequest()->IsDebug())
      throw new strayExceptionError('[Fw|Fatal] ' . $msg);
  }

  public function FwDebug($msg)
  {
    $this->_Write('[Fw|Debug] ' . $msg);
  }

  public function Error($msg)
  {
    $this->_Write('[User|Error] ' . $msg);
    if (true === strayRoutingBootstrap::fGetInstance()->GetRequest()->IsDebug())
      throw new strayExceptionError('[User|Error] ' . $msg);
  }

  public function Notice($msg)
  {
    $this->_Write('[User|Notice] ' . $msg);
  }
}
