<?php
/**
 * @brief Model form handler abstract class.
 * @author nekith@gmail.com
 * @abstract
 */

abstract class strayFormAModel extends strayFormABasic
{
  /**
   * Associated table instance.
   * @var strayModelsATable
   */
  protected $_table;

  /**
   * Initialization of the form.
   */
  protected function _InitForm()
  {
    $this->_InitFormModel();
    foreach ($this->fields as $field)
    {
      if (null == $field->GetValue() && 0 === strpos($field->name, 'm_'))
        $field->SetValue($this->_table->{'Get' . ucfirst(substr($field->name, 2))}());
    }
  }

  /**
   * Initialization of the model form.
   */
  abstract protected function _InitFormModel();

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
          if (0 === strpos($key, 'm_'))
            $method = 'Validate' . ucfirst(substr($key, 2));
          else
            $method = 'Validate' . ucfirst($key);
          if (true === method_exists($this, $method))
          {
            if (false === $this->$method($request->post->vars[$elem->name], $elem))
              $wrong = true;
            if (0 === strpos($key, 'm_'))
              $elem->AddNotice($this->_table->GetErrors()[substr($key, 2)]);
            }
          elseif (0 === strpos($key, 'm_'))
          {
            if (true === method_exists($this->_table, $method))
              if (false === $this->_table->$method($request->post->vars[$elem->name]))
              {
                $wrong = true;
                $elem->AddNotice($this->_table->GetErrors()[substr($key, 2)]);
              }
          }
          $elem->SetValue($request->post->vars[$elem->name]);
        }
        if (false === $wrong && true === $this->PostValidate($data))
          return true;
      }
    }
    return false;
  }

  /**
   * Save fields data to model object.
   * @param array $data fields
   */
  public function SaveToModel(array $data)
  {
    foreach ($data as $key => $elem)
      if (0 === strpos($key, 'm_'))
      {
        $method = 'Set' . ucfirst(substr($key, 2));
        if (true === method_exists($this->_table, $method))
          $this->_table->$method($elem->GetValue());
      }
  }

  /**
   * Add a new model field to the form. Call parent AddField.
   * @param strayFormAField $field new field
   */
  public function AddModelField(strayFormAField $field)
  {
    $field->name = 'm_' . $field->name;
    $this->AddField($field);
  }
}
