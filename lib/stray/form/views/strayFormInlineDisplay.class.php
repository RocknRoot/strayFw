<?php
/**
 * @brief Display class for inline fake-widget form.
 * @author nekith@gmail.com
 */

class strayFormInlineDisplay extends strayAppsInlineDisplay
{
  /**
   * Render the display.
   */
  public function Render()
  {
    if (null == $this->_layout)
      require STRAY_PATH_TO_LIB . 'stray/form/views/default.layout.php';
    else
      require $this->_pathWidget . 'views/' . $this->_layout . '.layout.php';
  }
}
