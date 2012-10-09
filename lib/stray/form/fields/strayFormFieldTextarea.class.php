<?php
/**
 * @brief Textarea field for forms.
 * @author nekith@gmail.com
 */

class strayFormFieldTextarea extends strayFormAField
{
  /**
   * Field label.
   * @var string
   */
  public $label;
  /**
   * Number of rows maximum.
   * @var int
   */
  public $rows;
  /**
   * Number of columns maximum.
   * @var int
   */
  public $cols;
  /**
   * Readonly mode.
   * @var bool
   */
  public $readOnly;

  /**
   * Constructor.
   * @param string $name name
   * @param string $label label
   * @param string $value value
   * @param int $rows maximum rows
   * @param int $cols maximum columns
   * @param bool $readOnly enable read only mode
   */
  public function __construct($name, $label, $value = null, $rows = null,
      $cols = null, $readOnly = false)
  {
    parent::__construct($name);
    $this->label = $label;
    $this->_value = $value;
    $this->rows = $rows;
    $this->cols = $cols;
    $this->readOnly = $readOnly;
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
    echo $separator . '<textarea name="' . $this->id
      . '" id="' . $this->id
      . '" class="' . $this->class . '"';
    if (true === isset($this->rows))
      echo ' rows="' . $this->rows . '"';
    if (true === isset($this->cols))
      echo ' cols="' . $this->cols . '"';
    if (true === $this->readOnly)
      echo ' readonly';
    echo '>';
    if (true === isset($this->_value))
      echo $this->_value;
    echo '</textarea>';
  }

  /**
   * Called when a field with same name already exists.
   * @param strayFormAField $oldOne old field
   * @return bool if false, merging fails
   */
  public function Merge(strayFormAField $oldOne)
  {
    if ($oldOne instanceof strayFormFieldTextarea
        || $oldOne instanceof strayFormFieldText
        || $oldOne instanceof strayFormFieldPassword)
    {
      $this->label = $oldOne->label;
      $this->_value = $oldOne->_value;
    }
  }
}
