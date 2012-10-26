<?php
/**
 * @brief Template render class.
 * @author nekith@gmail.com
 */

class strayAppsRenderTemplate extends strayAppsARender
{
  /**
   * Template to be rendered.
   * @var string
   */
  private $_template;

  /**
   * Construct.
   * @param strayAppsWidgetAViews $view calling view
   * @param string $template template path
   */
  public function __construct(strayAppsWidgetAViews $view, $template)
  {
    parent::__construct($view);
    $this->_template = $template;
  }

  /**
   * Return the generated display.
   * @return string generated display content
   */
  public function Render()
  {
    $env = strayExtTwig::fGetInstance()->GetEnvironment($this->_view->GetPathApp() . '/templates');
    return strayExtTwig::fGetInstance()->LoadTemplate($env, $this->_template, $this->vars);
  }
}
