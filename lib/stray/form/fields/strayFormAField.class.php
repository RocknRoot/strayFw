<?php
/**
 * @brief Abstract class for form fields.
 * @author nekith@gmail.com
 * @abstract
 */

abstract class strayFormAField
{
  #Types
  const TYPE_HIDDEN     = 'hidden';
  const TYPE_PASSWORD   = 'password';
  const TYPE_RADIO      = 'radio';
  const TYPE_SUBMIT     = 'submit';
  const TYPE_TEXT       = 'text';
  const TYPE_TEXTAREA   = 'textarea';

  #Render
  const RENDER_ALL            = 0;
  const RENDER_WITHOUT_LABEL  = 1;

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
  }

  /**
   * Set field tag id.
   * @param string $name form name
   */
  public function SetId($name)
  {
    $this->id = 'form' . ucfirst($name) . ucfirst($this->name);
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
   * @param string $separator label/input separator
   * @param int $flags render flags
   * @abstract
   */
  abstract public function Render($separator = null, $flags = self::RENDER_ALL);

  /**
   * Called when a field with same name already exists.
   * @param strayFormAField $oldOne old field
   * @abstract
   */
  abstract public function Merge(strayFormAField $oldOne);

  /**
   * Add notice to field.
   * @param string $msg notice content
   * @return bool false
   */
  public function AddNotice($msg)
  {
    $this->notices[] = $msg;
    return false;
  }
}
