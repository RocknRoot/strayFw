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
   * @param string $separator label/input separator
   * @param int $flags render flags
   * @param string $newLine new line string displayed between two option
   */
  public function Render($separator = null, $flags = self::RENDER_ALL, $newLine = '<br/>')
  {
    if ($flags != self::RENDER_WITHOUT_LABEL)
      echo $this->label;
    echo $separator;
    $content = null;
    foreach ($this->options as $key => $e)
    {
      $content .= '<label><input type="radio" name="' . $this->id
        . '" id="' . $this->id . '" class="' . $this->class . '" value="' . $key . '"';
      if ($this->_value == $key)
        $content .= ' checked';
      $content .= '/>' . $e . '</label>' . $newLine;
    }
    echo substr($content, 0, -strlen($newLine));
  }

  /**
   * Called when a field with same name already exists.
   * @param strayFormAField $oldOne old field
   */
  public function Merge(strayFormAField $oldOne)
  {
    if ($oldOne instanceof strayFormFieldText
        || $oldOne instanceof strayFormFieldPassword
        || $oldOne instanceof strayFormFieldTextarea)
    {
      $this->label = $oldOne->label;
      $this->_value = $oldOne->_value;
    }
  }
}
