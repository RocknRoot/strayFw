<?php
/**
 * @brief Hidden field for forms.
 * @author nekith@gmail.com
 */

class strayFormFieldHidden extends strayFormAField
{
  /**
   * Constructor.
   * @param string $name name
   * @param string $value value
   */
  public function __construct($name, $value = null)
  {
    parent::__construct($name);
    $this->_value = $value;
  }

  /**
   * Render the field display code.
   */
  public function Render()
  {
    $content = '<input type="hidden" name="' . $this->name
        . '" id="' . $this->id . '"';
    if (true === isset($this->_value))
      $content .= ' value="' . $this->_value . '"';
    $content .= '/>';
    return $content;
  }

  /**
   * Render label tag.
   * @return string generated render
   */
  public function RenderLabel()
  {
    return null;
  }
}
