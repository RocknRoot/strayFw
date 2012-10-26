<?php
/**
 * @brief JSON render class.
 * @author nekith@gmail.com
 */

class strayAppsRenderJSON extends strayAppsARender
{
  /**
   * Data to be encoded.
   * @var mixed
   */
  private $_data;

  /**
   * Construct.
   * @param strayAppsWidgetAViews $view calling view
   * @param mixed $data data to be encoded
   */
  public function __construct(strayAppsWidgetAViews $view, $data)
  {
    parent::__construct($view);
    $this->_data = $data;
  }

  /**
   * Return the generated display.
   * @return string generated display content
   */
  public function Render()
  {
    return json_encode($data,  JSON_PRETTY_PRINT);
  }
}
