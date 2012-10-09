<?php
/**
 * @brief Allows to perform sub_query logical operations.
 * @abstract
 * @author nekith@gmail.com
 */
abstract class strayModelsQueryAOpe
{
  /**
   * Get the operator corresponding SQL code.
   * @return string SQL
   */
  abstract public function ToSql();
}
