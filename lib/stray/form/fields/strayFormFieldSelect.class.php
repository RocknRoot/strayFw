<?php
/**
 * @brief Select (and options) field for forms.
 * @author nekith@gmail.com
 */

class strayFormFieldSelect extends strayFormAField
{
  /**
   * Field label.
   * @var string
   */
  public $label;
  /**
   * Field options.
   * @var array
   */
  public $options;

  /**
   * Constructor.
   * @param string $name name
   * @param string $label label
   * @param array $values options
   */
  public function __construct($name, $label, array $values)
  {
    parent::__construct($name);
    $this->label = $label;
    $this->options = $values;
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
    echo $separator . '<select name="' . $this->id
      . '" id="' . $this->id
      . '" class="' . $this->class . '"';
    echo '>';
    foreach ($this->options as $key => $elem)
    {
      echo '<option value="' . $key . '"';
      if ($key == $this->_value)
        echo ' selected="selected"';
      echo '>' . $elem . '</option>';
    }
    echo '</select>';
  }

  /**
   * Called when a field with same name already exists.
   * @param strayFormAField $oldOne old field
   */
  public function Merge(strayFormAField $oldOne)
  {
    if ($oldOne instanceof strayFormFieldSelect)
    {
      $this->options = $oldOne->options;
      $this->_value = $oldOne->_value;
    }
  }
}
