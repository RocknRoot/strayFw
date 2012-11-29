<?php
/**
 * @brief Form handler abstract class.
 * @author nekith@gmail.com
 * @abstract
 */

abstract class strayFormABasic
{
  /**
   * Fields to be generated.
   * @var array
   */
  public $fields;
  /**
   * Form notices.
   * @var array
   */
  public $notices;
  
  /**
   * Construct.
   */
  public function  __construct()
  {
    $this->fields = array();
    $this->_InitForm();
  }

  /**
   * Form initialization.
   */
  abstract protected function _InitForm();

  /**
   * Called before fields validations.
   * @return bool true if valid
   */
  public function PreValidate()
  {
    return true;
  }

  /**
   * Called before fields validations.
   * @return bool true if valid
   */
  public function PostValidate()
  {
    return true;
  }

  /**
   * Validate of the all form.
   * @param strayRoutingRequest $request current request
   * @return bool true if valid
   */
  public function Validate(strayRoutingRequest $request)
  {
    $data = $this->fields;
    if (true === is_array($data))
    {
      if (true === $this->PreValidate())
      {
        $wrong = false;
        foreach ($data as $key => $elem)
        {
          $method = 'Validate' . ucfirst($key);
          if (true === method_exists($this, $method))
            if (false === $this->$method($request->post->vars[$elem->name], $elem))
              $wrong = true;
          $elem->SetValue($request->post->vars[$elem->name]);
        }
        if (false === $wrong && true === $this->PostValidate())
          return true;
      }
    }
    return false;
  }

  /**
   * Add notice to form.
   * @param string $msg notice content
   */
  public function AddNotice($msg)
  {
    $this->notices[] = $msg;
  }

  /**
   * Add a new field to the form.
   * @param strayFormAField $field new field
   */
  public function AddField(strayFormAField $field)
  {
    $this->fields[$field->name] = $field;
  }
}
