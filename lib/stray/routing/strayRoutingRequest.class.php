<?php
/**
 * @brief Routing request container class.
 * @author nekith@gmail.com
 */

class strayRoutingRequest
{
  /**
   * App name.
   * @var string
   */
  public $app;
  /**
   * Widget name.
   * @var string
   */
  public $widget;
  /**
   * View name.
   * @var string
   */
  public $view;
  /**
   * Params values.
   * @var array
   */
  public $params;
  /**
   * POST values.
   * @var strayRoutingRequestPost
   */
  public $post;

  /**
   * Constructor.
   */
  public function __construct()
  {
    $this->post = new strayRoutingRequestPost();
    $this->params = array();
  }
}
