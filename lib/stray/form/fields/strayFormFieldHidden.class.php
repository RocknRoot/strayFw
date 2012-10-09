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
   * @param string $separator label/input separator
   * @param int $flags render flags
   */
  public function Render($separator = null, $flags = self::RENDER_ALL)
  {
    echo $separator . '<input type="hidden" name="' . $this->id
        . '" id="' . $this->id . '"';
    if (true === isset($this->_value))
      echo ' value="' . $this->_value . '"';
    echo '/>';
  }

  /**
   * Called when a field with same name already exists.
   * @param strayFormAField $oldOne old field
   */
  public function Merge(strayFormAField $oldOne)
  {
    if ($oldOne instanceof strayFormFieldHidden)
    {
      $this->_value = $oldOne->_value;
    }
  }
}
