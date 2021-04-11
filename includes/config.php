<?php 
  // Non-production error reporting
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);

  set_include_path(dirname(__FILE__));

  session_start();
?>