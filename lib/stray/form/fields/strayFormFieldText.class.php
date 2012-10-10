<?php
/**
 * @brief Text field for forms.
 * @author nekith@gmail.com
 */

class strayFormFieldText extends strayFormAField
{
  /**
   * Field label.
   * @var string
   */
  public $label;
  /**
   * Field max length.
   * @var int
   */
  public $maxLength;

  /**
   * Constructor.
   * @param string $name name
   * @param string $label label
   * @param string $value value
   * @param int $maxLength max length
   */
  public function __construct($name, $label, $value = null, $maxLength = null)
  {
    parent::__construct($name);
    $this->label = $label;
    $this->_value = $value;
    $this->maxLength = $maxLength;
  }

  /**
   * Render the field display code.
   */
  public function Render()
  {
    echo '<input type="text" name="' . $this->name
      . '" id="' . $this->id
      . '" class="' . $this->class . '"';
    if (true === isset($this->_value))
      echo ' value="' . $this->_value . '"';
    if (true === isset($this->maxLength))
      echo ' maxlength="' . $this->maxLength . '"';
    echo '/>';
  }

  /**
   * Render label tag.
   * @return string generated render
   */
  public function RenderLabel()
  {
    return '<label for="' . $this->id . '">' . $this->label . '</label>';
  }
}
