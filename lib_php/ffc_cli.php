<?php
/* Command-line script for testing functionality */
// jmht need as running from command-line with no timezone setup
date_default_timezone_set('Europe/London');
require __DIR__ . '/vendor/autoload.php';
include('ffc2.php');

if (php_sapi_name() != 'cli') {
    throw new Exception('This application must be run on the command line.');
}

$html = "";
try {
      $columns = get_spreadsheet_columns();
      $user_login = 'ms_hardy';
      $html =  make_table_html($user_login, $columns);
    } catch(Exception $e) {
      $html = "<p class=\"ffc_error\">Error getting user status table: " . $e->getMessage() . "</p>\n";
  }
echo $html;
