<?php
/**
 * @brief Base for all renders.
 * @author nekith@gmail.com
 * @abstract
 */

abstract class strayAppsARender
{
  /**
   * Vars container.
   * @var array
   */
  public $vars;
  /**
   * Widget view.
   * @var strayAppsWidgetAView
   */
  protected $_view;

  /**
   * Construct.
   * @param strayAppsWidgetAView $view calling view
   */
  public function __construct(strayAppsWidgetAView $view)
  {
    $this->_view = $view;
  }

  /**
   * Return the generated display.
   * @return string generated display content
   * @abstract
   */
  abstract public function Render();
}
