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
   */
  public function Render()
  {
    $content = '<textarea name="' . $this->name
      . '" id="' . $this->id
      . '" class="' . $this->class . '"';
    if (true === isset($this->rows))
      $content .= ' rows="' . $this->rows . '"';
    if (true === isset($this->cols))
      $content .= ' cols="' . $this->cols . '"';
    if (true === $this->readOnly)
      $content .= ' readonly';
    $content .= '>';
    if (true === isset($this->_value))
      $content .= $this->_value;
    $content .= '</textarea>';
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
