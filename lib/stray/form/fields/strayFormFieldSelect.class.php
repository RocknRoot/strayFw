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
   */
  public function Render()
  {
    $content = '<select name="' . $this->name
      . '" id="' . $this->id
      . '" class="' . $this->class . '"';
    $content .= '>';
    foreach ($this->options as $key => $elem)
    {
      $content .= '<option value="' . $key . '"';
      if ($key == $this->_value)
        $content .= ' selected="selected"';
      $content .= '>' . $elem . '</option>';
    }
    $content .= '</select>';
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
