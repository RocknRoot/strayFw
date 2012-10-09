<?php
/**
 * @brief Password field for forms.
 * @author nekith@gmail.com
 */

class strayFormFieldPassword extends strayFormAField
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
   * @param string $separator label/input separator
   * @param int $flags render flags
   */
  public function Render($separator = null, $flags = self::RENDER_ALL)
  {
    if ($flags != self::RENDER_WITHOUT_LABEL)
      echo '<label for="' . $this->id . '">' . $this->label . '</label>';
    echo $separator . '<input type="password" name="' . $this->id
      . '" id="' . $this->id
      . '" class="' . $this->class . '"';
    if (true === isset($this->_value))
      echo ' value="' . $this->_value . '"';
    if (true === isset($this->maxLength))
      echo ' maxlength="' . $this->maxLength . '"';
    echo '/>';
  }

  /**
   * Called when a field with same name already exists.
   * @param strayFormAField $oldOne old field
   */
  public function Merge(strayFormAField $oldOne)
  {
    if ($oldOne instanceof strayFormFieldPassword
        || $oldOne instanceof strayFormFieldText
        || $oldOne instanceof strayFormFieldTextarea)
    {
      $this->label = $oldOne->label;
      $this->_value = $oldOne->_value;
    }
  }
}
