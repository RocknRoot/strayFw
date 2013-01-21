<?php
/**
 * @brief Displaying the command list.
 * @author nekith@gmail.com
 */
function strayfCommandHelp()
{
  echo "Help screen - Commands:\n";
  // applications
  echo "\nApplications:\n   app:create name\n";
  // migrations
  echo "\nMigrations:\n   migration:create db_name migration_name\n"
      . "   migration:help db_name migration_name\n"
      . "   migration:migrate [db_name]\n"
      . "   migration:rewind db_name migration_name\n";
  // models
  echo "\nModels:\n   model:create db_name\n"
      . "   model:generate [db_name]\n";
  echo "\nPlugins:\n   plugin:create plugin_name\n";
  // sql
  echo "\nSQL:\n   sql:build [db_name]\n";
  // widgets
  echo "\nWidgets:\n   widget:create app_name widget_name\n"
      . "   widget:script app_name widget_name script_name [script_arguments]\n";
  // -
  echo "\n";
}
