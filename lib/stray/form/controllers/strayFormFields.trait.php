<?php
/**
 * @brief Fiels containing.
 * @author nekith@gmail.com
 */

trait strayFormFields
{
  /**
   * Init fields container.
   */
  protected function _InitFields()
  {
    $this->_fields = array();
  }

  /**
   * Add a new field to the form. If contains a field with the same name,
   * call Merge method of the new one.
   * @param strayFormAField $field new field
   */
  public function AddField(strayFormAField $field)
  {
    parent::AddField();
  }
}
