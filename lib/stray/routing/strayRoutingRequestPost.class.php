<?php
/**
 * @brief Routing request POST values.
 * @author nekith@gmail.com
 */

class strayRoutingRequestPost
{
  /**
   * If true, uses htmlentities instead of htmlspecialchars for HTMLFilter.
   * @var bool
   */
  public $htmlFilterHardMode;
  /**
   * POST values.
   * @var array
   */
  public $vars;

  /**
   * Constructor.
   */
  public function __construct()
  {
    $this->htmlFilterHardMode = false;
    $this->vars = array();
    //parse POST
    foreach ($_POST as $key => $e)
      $this->vars[$key] = $e;
  }

  /**
   * Get tags stripped POST value.
   * @param string $var POST value name
   * @param string $strip tags to be stripped
   * @return string tags stripped value
   */
  public function StripTags($var, $strip = null)
  {
    return strip_tags($this->vars[$var], $strip);
  }

  /**
   * Get HTML filtered POST value.
   * @param string $var POST value name
   * @return string HTML filtered value
   */
  public function HTMLFilter($var)
  {
    if (true === $this->htmlFilterHardMode)
      return htmlentities($this->vars[$var]);
    return htmlspecialchars($this->vars[$var]);
  }
}
