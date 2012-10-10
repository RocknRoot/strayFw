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
   * @param array $data form data
   * @return bool true if valid
   */
  public function PreValidate(array $data)
  {
    return true;
  }

  /**
   * Called before fields validations.
   * @param array $data form data
   * @return bool true if valid
   */
  public function PostValidate(array $data)
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
      if (true === $this->PreValidate($data))
      {
        $wrong = false;
        foreach ($data as $key => $elem)
        {
          $method = 'Validate' . ucfirst($key);
          if (true === method_exists($this, $method))
            if (false === $this->$method($request->post->vars[$elem->id], $elem))
              $wrong = true;
          $elem->SetValue($request->post->vars[$elem->id]);
        }
        if (false === $wrong && true === $this->PostValidate($data))
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
