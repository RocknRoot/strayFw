<?php
/**
 * @brief Checkbox field for forms.
 * @author nekith@gmail.com
 */

class strayFormFieldCheckbox extends strayFormAField
{
  /**
   * Field label.
   * @var string
   */
  public $label;
  /**
   * True if already checked at the form creation.
   * @var bool
   */
  public $checked;

  /**
   * Constructor.
   * @param string $name name
   * @param string $label label
   * @param string $value value
   * @param bool $checked checked state
   */
  public function __construct($name, $label, $value = null, $checked = false)
  {
    parent::__construct($name);
    $this->label = $label;
    $this->SetValue($value);
    $this->checked = $checked;
  }

  /**
   * Set field value. Redefinition checkbox specific values.
   * @param string $value new value
   */
  public function SetValue($value)
  {
    if (true === is_bool($value))
      $this->_value = $value;
    else
    {
      if (null != $value && $value != '0')
        $this->_value = true;
      else
        $this->_value = false;
    }
  }

  /**
   * Render the field display code.
   * @param string $separator label/input separator
   * @param int $flags render flags
   */
  public function Render()
  {
    $content = '<input type="checkbox" name="' . $this->name
      . '" id="' . $this->id
      . '" class="' . $this->class . '"';
    if (true === $this->_value || true === $this->checked)
      $content .= ' checked';
    $content .= '/>';
    return $content;
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
