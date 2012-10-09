<?php
/**
 * @brief This is the command line class.
 * @final
 * @author nekith@gmail.com
 */

final class strayCommand
{
  /**
   * Current order section.
   * @var string
   */
  private $_orderSection;
  /**
   * Current order action.
   * @var string
   */
  private $_orderAction;
  /**
   * Current order parameters.
   * @var array
   */
  private $_params;
  /**
   * Current order options.
   * @var array
   */
  private $_options;

  /**
   * Parse the command line arguments and generate an order.
   */
  public function Parse()
  {
    if ($_SERVER['argc'] < 2 || false === strpos($_SERVER['argv'][1], ':'))
      strayfCommandHelp();
    else
    {
      // section & action
      array_shift($_SERVER['argv']);
      list($section, $action) = explode(':', $_SERVER['argv'][0]);
      $this->_orderSection = 'strayCommandOrders' . ucfirst($section);
      $this->_orderAction = 'f' . ucfirst($action);
      // params and options
      array_shift($_SERVER['argv']);
      foreach ($_SERVER['argv'] as $arg)
      {
        if (substr($arg, 0, 2) == '--')
        {
          $pos = strpos($arg, '=');
          if (false === $eq)
          {
            $key = substr($arg, 2);
            if (false === isset($this->_options[$key]))
              $this->_options[$key] = true;
          }
          else
          {
            $key = substr($arg, 2, $pos - 2);
            $this->_options[$key] = substr($arg, $pos + 1);
          }
        }
        elseif (substr($arg, 0, 1) == '-')
        {
          $chars = str_split(substr($arg, 1));
          foreach ($chars as $c)
          {
            $key = $c;
            $this->_options[$key] = isset($this->_options[$key]) ? false : true;
          }
        }
        else
        {
          $this->_params[] = $arg;
        }
      }
    }
  }

  /**
   * Execute the order.
   */
  public function Run()
  {
    if (false === empty($this->_orderSection)
        && false === empty($this->_orderAction))
    {
      if (true === method_exists($this->_orderSection, $this->_orderAction))
        call_user_func(array($this->_orderSection, $this->_orderAction),
            $this->_params, $this->_options);
      else
        throw new strayExceptionFatal('unknown command');
    }
  }
}
