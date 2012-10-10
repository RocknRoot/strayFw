<?php
/**
 * @brief Radio field for forms.
 * @author nekith@gmail.com
 */

class strayFormFieldRadio extends strayFormAField
{
  /**
   * Field label.
   * @var string
   */
  public $label;
  /**
   * Field options.
   * @var string
   */
  public $options;

  /**
   * Constructor.
   * @param string $name name
   * @param string $label label
   * @param array $options options (value => text)
   * @param string $default default option
   */
  public function __construct($name, $label, array $options, $default = null)
  {
    parent::__construct($name);
    $this->label = $label;
    $this->options = $options;
    $this->_value = $default;
  }

  /**
   * Render the field display code.
   */
  public function Render()
  {
    $content = null;
    foreach ($this->options as $key => $e)
    {
      $content .= '<label><input type="radio" name="' . $this->name
        . '" id="' . $this->id . '" class="' . $this->class . '" value="' . $key . '"';
      if ($this->_value == $key)
        $content .= ' checked';
      $content .= '/>' . $e . '</label>';
    }
    return $content;
  }
  /**
   * Render label tag.
   * @return string generated render
   */
  public function RenderLabel()
  {
    return $this->label;
  }
}
