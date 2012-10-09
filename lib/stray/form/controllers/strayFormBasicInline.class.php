<?php
/**
 * @brief Form handle inline class.
 * @author nekith@gmail.com
 */

class strayFormBasicInline extends strayAppsInline
{
  /**
   * Routing request for form validation.
   * @var strayRoutingRequest
   */
  public $destinationRequest;
  /**
   * Fields to be generated.
   * @var array
   */
  protected $_fields;
  /**
   * Custom "POST" values.
   * @var array
   */
  public $post;

  /**
   * Construct.
   */
  public function __construct(strayAppsAWidget $parent, $name, $action = 'index', array $params = null, array $options = null)
  {
    parent::__construct($parent, $name, $action, $params, $options);
    $this->_display = new strayFormInlineDisplay($parent->GetDisplay());
    $this->_display->vars->formName = $this->_requestInfos->name;
    $this->destinationRequest = clone $this->_parent->GetRequest();
    if (($parent instanceof strayAppsModule))
    {
      $this->destinationRequest->module->options->$name = 'valid';
    }
    else
    {
      // TODO can't auto edit request, don't know how to do exactly
    }
    $this->destinationRequest->BuildString();
    $this->_display->vars->formDestination = $this->destinationRequest->entireString;
    $this->_display->vars->formNotices = array();
    $this->_fields = array();
    // built-in actions
    $this->SetAction('index', function(strayFormBasicInline $inline) {
      $inline->GetDisplay()->vars->formFields = $inline->_fields;
    });
    $this->SetAction('valid', function(strayFormBasicInline $inline) {
      $inline->GetDisplay()->vars->formFields = $inline->_fields;
      $inline->Validate();
    });
    $this->post = null;
  }

  /**
   * Validate of the all form.
   * @return bool false if not successfull
   */
  public function Validate()
  {
    if (null === $this->post)
      $this->post = $this->_parent->GetRequest()->post->vars;
    if (false === is_array($this->_fields))
      return false;
    foreach ($this->_fields as $key => $elem)
      $elem->SetValue($this->post[$elem->id]);
    if (true === isset($this->_actions->postvalidate))
    {
      $f = $this->_actions->postvalidate;
      if (false === $f($this, $data))
        return false;
    }
    if (true === isset($this->_actions->success))
    {
      $f = $this->_actions->success;
      $f($this, $data);
    }
    return true;
  }

  /**
   * Set PostValidate function.
   * @param Closure $function function
   * @return bool true if successfully set
   */
  public function SetPostValidate(Closure $function)
  {
    $ref = new ReflectionFunction($function);
    if (2 > $ref->getNumberOfParameters())
    {
      strayLog::fGetInstance()->Notice('strayFormBasicInline::SetPostValidate : specified closure takes less than 2 arguments');
      return false;
    }
    $this->_actions->postvalidate = $function;
    return true;
  }

  /**
   * Set ValidSuccess function.
   * @param Closure $function function
   * @return bool true if successfully set
   */
  public function SetValidSuccess(Closure $function)
  {
    $ref = new ReflectionFunction($function);
    if (2 > $ref->getNumberOfParameters())
    {
      strayLog::fGetInstance()->Notice('strayFormBasicInline::SetValidSuccess : specified closure takes less than 2 arguments');
      return false;
    }
    $this->_actions->success = $function;
    return true;
  }

  /**
   * Add notice to form.
   * @param string $msg notice content
   */
  public function AddNotice($msg)
  {
    $notices = $this->_display->vars->formNotices;
    $notices[] = $msg;
    $this->_display->vars->formNotices = $notices;
  }

  /**
   * Add a new field to the form. If contains a field with the same name,
   * call Merge method of the new one.
   * @param strayFormAField $field new field
   */
  public function AddField(strayFormAField $field)
  {
    $field->SetId($this->_display->vars->formName);
    $name = $field->name;
    if (true === isset($this->_fields->$name))
      $field->Merge($this->_fields->$name);
    $this->_fields->$name = $field;
  }
}
