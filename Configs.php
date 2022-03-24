<?php
  /*** GENERAL SETTING FOR DAEMON AND PRINTERS ***/
  // DAEMON SETTINGS
  // Printer TO watch (and identification)
  define('PRINTER_ID', 2); //la impresora en uso (numerada en BD)

  // Cycle
  define('LOOP_CYCLE', 1); // Ciclo determinado en segundos, en este caso cada segundo

  // Error and reporting
  ini_set("display_errors", 1);
  error_reporting(E_ALL & ~E_NOTICE);
  define('LOG_KEEP', true);
  define('LOG_FILE', 'daemon.log');
  
  function addlog ($message="") {
    error_log( "[" . date("Y-m-d H:i:s") . "] " . $message . PHP_EOL, 3, LOG_FILE );
  }








  
?>