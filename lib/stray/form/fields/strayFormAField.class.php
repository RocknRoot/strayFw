<?php
/**
 * @brief Abstract class for form fields.
 * @author nekith@gmail.com
 * @abstract
 */

abstract class strayFormAField
{
  /**
   * Field name.
   * @var string
   */
  public $name;
  /**
   * Field tag id.
   * @var string
   */
  public $id;
  /**
   * Error notices to be displayed next to the field.
   * @var array
   */
  public $notices;
  /**
   * Field class.
   * @var string
   */ 
  public $class;
  /**
   * Field value.
   * @var string
   */
  protected $_value;

  /**
   * Constructor.
   * @param string $name field name
   */
  public function __construct($name)
  {
    $this->name = $name;
    $this->id = 'field_' . $name;
  }

  /**
   * Get field value.
   * @return string value
   */
  public function GetValue()
  {
    return $this->_value;
  }

  /**
   * Set field value.
   * @param string $value new value
   */
  public function SetValue($value)
  {
    $this->_value = $value;
  }

  /**
   * Render the field display code.
   * @abstract
   */
  abstract public function Render();

  /**
   * Render label tag.
   * @return string generated render
   * @abstract
   */
  abstract public function RenderLabel();

  /**
   * Add notice to field.
   * @param string $msg notice content
   */
  public function AddNotice($msg)
  {
    $this->notices[] = $msg;
  }

  /**
   * Helper for templates.
   * @param string $name called function
   * @param array $args arguments
   * @return string generated render
   */
  public function __call($name, array $args)
  {
    if ('render_label' == $name)
      return $this->RenderLabel();
    return null;
  }
}
