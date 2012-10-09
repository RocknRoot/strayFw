<?php
/**
 * @brief Ask the user confirmation for a CLI command.
 * @return bool true if yes
 * @author nekith@gmail.com
 */
function strayfCommandAskConfirm($question)
{
  $c = null;
  do
  {
    if ($c == 'y')
      return true;
    if ($c == 'n')
      return false;
    echo $question . ' [y/n] : ';
  } while (false !== ($c = fgetc(STDIN)));
  return false;
}