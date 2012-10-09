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
  public function InitForm()
  {
    $this->InitModel();
    foreach ($this->_fields->Get() as $field)
    {
      if (null == $field->GetValue() && 0 === strpos($field->name, 'm_'))
        $field->SetValue($this->_table->{'Get' . ucfirst(substr($field->name, 2))}());
    }
  }

  /**
   * Initialization of the model form.
   */
  abstract public function InitModel();

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
          if (0 === strpos($key, 'm_'))
            $method = 'Validate' . ucfirst(substr($key, 2));
          else
            $method = 'Validate' . ucfirst($key);
          if (true === method_exists($this, $method))
          {
            if (false === $this->$method($this->_request->post->vars->{$elem->id}, $elem))
              $wrong = true;
            if (0 === strpos($key, 'm_'))
              $elem->AddNotice($this->_table->GetErrors()->{substr($key, 2)});
            }
          elseif (0 === strpos($key, 'm_'))
          {
            if (true === method_exists($this->_table, $method))
              if (false === $this->_table->$method($this->_request->post->vars->{$elem->id}))
              {
                $wrong = true;
                $elem->AddNotice($this->_table->GetErrors()->{substr($key, 2)});
              }
          }
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
