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
  public function Render($separator = null, $flags = self::RENDER_ALL)
  {
    if ($flags != self::RENDER_WITHOUT_LABEL)
      echo '<label for="' . $this->id . '">' . $this->label . '</label>';
    echo $separator . '<input type="checkbox" name="' . $this->id
      . '" id="' . $this->id
      . '" class="' . $this->class . '"';
    if (true === $this->_value || true === $this->checked)
      echo ' checked';
    echo '/>';
  }

  /**
   * Called when a field with same name already exists.
   * @param strayFormAField $oldOne old field
   */
  public function Merge(strayFormAField $oldOne)
  {
    if ($oldOne instanceof strayFormFieldCheckbox)
    {
      $this->label = $oldOne->label;
      $this->_value = $oldOne->_value;
      $this->checked = $oldOne->checked;
    }
  }
}
