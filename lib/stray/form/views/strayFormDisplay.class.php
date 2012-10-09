<?php
/**
 * @brief Forms specific display class.
 * @author nekith@gmail.com
 */

class strayFormDisplay extends strayAppsComponentDisplay
{
  /**
   * Constructor.
   * @param string $pathApp application directory path
   * @param string $pathModule module directory path
   * @param string $pathCss CSS directory path
   */
  public function __construct($pathModule, $pathApp, $pathCss)
  {
    parent::__construct($pathModule, $pathApp, $pathCss);
    $this->_layout = null;
  }

  /**
   * Render the display.
   */
  public function Render()
  {
    if (true === $this->defaultLayout)
    {
      $this->defaultLayout = false;
      require $this->_pathWidget . 'views/default.layout.php';
    }
    else if (null != $this->_layout)
      require $this->_pathWidget . 'views/' . $this->_layout . '.layout.php';
    else
      require STRAY_PATH_TO_LIB . 'stray/form/views/default.layout.php';
  }
}
