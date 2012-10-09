<?php
/**
 * @brief Form handler abstract class.
 * @author nekith@gmail.com
 * @abstract
 */

abstract class strayFormABasic extends strayAppsComponent
{
  /**
   * Fields to be generated.
   * @var array
   */
  protected $_fields;
  /**
   * Routing request for form validation.
   * @var strayRoutingRequest
   */
  public $destinationRequest;
  
  /**
   * Construct with current routing request.
   * @param strayRoutingRequest $request routing request
   * @param int $index routing request component index
   */
  public function  __construct(strayRoutingRequest $request, $index)
  {
    parent::__construct($request, $index);
    $this->_display = new strayFormDisplay(STRAY_PATH_TO_APPS . $request->app
        . '/components/' . $request->components[$index]->name . '/',
        STRAY_PATH_TO_APPS . $request->app . '/',
        STRAY_PATH_TO_WEB . 'css/' . $request->app . '/components/'
        . $request->components[$index]->name . '/');
    $this->destinationRequest = $request->BuildWithComponent(
        new strayRoutingRequestInfos($request->components[$index]->name, 'valid',
          $request->components[$index]->params, $request->components[$index]->options));
    $this->_display->vars->formName = $request->components[$index]->name;
    $this->_display->vars->formDestination = $this->destinationRequest->entireString;
    $this->_display->vars->formNotices = array();
    $this->_fields = array();
    $this->InitForm();
  }

  /**
   * Form initialization.
   */
  abstract public function InitForm();

  /**
   * Run the component logic.
   * @return strayAppsADisplay associated display
   */
  public function Run()
  {
    parent::Run();
    $this->_display->vars->formFields = $this->_fields;
    return $this->_display;
  }

  /**
   * Action index, called at form index.
   */
  public function IndexAction() {}

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
   * Action valid, called when form is sent.
   */
  public function ValidAction()
  {
    $this->Validate();
  }

  /**
   * Validate of the all form.
   */
  public function Validate()
  {
    $data = $this->_fields->Get();
    if (true === is_array($data))
    {
      if (true === $this->PreValidate($data))
      {
        $wrong = false;
        foreach ($data as $key => $elem)
        {
          $method = 'Validate' . ucfirst($key);
          if (true === method_exists($this, $method))
            if (false === $this->$method($this->_request->post->vars->{$elem->id}, $elem))
              $wrong = true;
          $elem->SetValue($this->_request->post->vars->{$elem->id});
        }
        if (true === $wrong || false === $this->PostValidate($data))
          $this->_ValidError($data);
        else
          $this->_ValidSuccess($data);
      }
    }
  }

  /**
   * Called if form is not valid.
   */
  protected function _ValidError(array $data)
  {
    $this->IndexAction();
  }

  /**
   * Called if form is valid.
   * @abstract
   */
  abstract protected function _ValidSuccess(array $data);

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
