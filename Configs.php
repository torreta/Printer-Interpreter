<?php
  /*** GENERAL SETTING FOR DAEMON AND PRINTERS ***/

  /***
   *  
   * HERE WE ARE SUPPOSED TO SET ALL THAT CONCERNS THE PRINTER AND DAEMON
   * 
  ***/

  // DAEMON SETTINGS
  // Printer TO watch (and identification)
  // CHECK on main proyect database to set this if theres more than one to watch
  // 1 daemon checks one printer....
  // this daemon can run on multiple system paths (check pwd)
  define('PRINTER_ID', 2); //la impresora en uso (numerada en BD)

  // Cycle (how ofter the daemon check the database)
  define('LOOP_CYCLE', 1); // Ciclo determinado en segundos, en este caso cada segundo

  // Error and reporting (file for log)(must exists or fails)
  ini_set("display_errors", 1);
  error_reporting(E_ALL & ~E_NOTICE);
  define('LOG_KEEP', true);
  define('LOG_FILE', 'daemon.log');
  
  function addlog ($message="") {
    error_log( "[" . date("Y-m-d H:i:s") . "] " . $message . PHP_EOL, 3, LOG_FILE );
  }

  // PRINER MODEL (a way to check if we can configure these variables by default)
  $Printer_model = "EMULATOR"; // <---- Check switch to set this

  // DEFAULT VALUES
  // you have to check this on the command manual from printer
  // prices
  $default_price_decimals = "00";
  $default_price_decimals_quantity = 2;
  $default_price_integer_quantity = 8;
    
  // quantity
  $default_quantity_decimals = "000";
  $default_quantity_decimals_quantity = 3;
  $default_quantity_integer_quantity = 5;

  // Texts
  $default_max_characters_description = 20;
  $default_max_characters_text = 40;
  $default_max_characters_info = 40;
  $default_max_characters_comments = 40;

  // MODEL CASES
  switch ($Printer_model) {
    case "EMULATOR":
      // prices
      $price_decimals = $default_price_decimals;
      $price_decimals_quantity = $default_price_decimals_quantity;
      $price_integer_quantity = $default_price_integer_quantity;
    
      // quantity
      $quantity_decimals = $default_quantity_decimals;
      $quantity_decimals_quantity = $default_quantity_decimals_quantity;
      $quantity_integer_quantity = $default_quantity_integer_quantity;
      // texts
      $max_characters_description = $default_max_characters_description;
      $max_characters_text = $default_max_characters_text;
      $max_characters_info = $default_max_characters_info;
      $max_characters_comments = $default_max_characters_comments;
    break;
    case "SRP-350":
      // prices
      $price_decimals = "00";
      $price_decimals_quantity = 2;
      $price_integer_quantity = 8;
    
      // quantity
      $quantity_decimals = "000";
      $quantity_decimals_quantity = 3;
      $quantity_integer_quantity = 5;
      // texts
      $max_characters_description = 20;
      $max_characters_text = 40;
      $max_characters_info = 40;
      $max_characters_comments = 40;
    break;
    case "BIXOLON":
      // prices
      $price_decimals = "00";
      $price_decimals_quantity = 2;
      $price_integer_quantity = 8;
    
      // quantity
      $quantity_decimals = "000";
      $quantity_decimals_quantity = 3;
      $quantity_integer_quantity = 5;
      // texts
      $max_characters_description = 20;
      $max_characters_text = 40;
      $max_characters_info = 40;
      $max_characters_comments = 40;
    break;
    case "HK80_VE":
      // prices
      $price_decimals = "00";
      $price_decimals_quantity = 2;
      $price_integer_quantity = 14;
    
      // quantity
      $quantity_decimals = "000";
      $quantity_decimals_quantity = 3;
      $quantity_integer_quantity = 14;

      // texts
      $max_characters_description = 20;
      $max_characters_text = 40;
      $max_characters_info = 40;
      $max_characters_comments = 40;
    break;

    default: // undetermined printer
      // prices
      $price_decimals = $default_price_decimals;
      $price_decimals_quantity = $default_price_decimals_quantity;
      $price_integer_quantity = $default_price_integer_quantity;
    
      // quantity
      $quantity_decimals = $default_quantity_decimals;
      $quantity_decimals_quantity = $default_quantity_decimals_quantity;
      $quantity_integer_quantity = $default_quantity_integer_quantity;
      // texts
      $max_characters_description = $default_max_characters_description;
      $max_characters_text = $default_max_characters_text;
      $max_characters_info = $default_max_characters_info;
      $max_characters_comments = $default_max_characters_comments;
    break;
  }

  //  Prices
  define('D_PRICE_DECIMALS',  $price_decimals);
  define('D_PRICE_DECIMALS_QUANTITY',  $price_decimals_quantity);
  define('D_PRICE_INTEGER_QUANTITY',  $price_integer_quantity);
  //  Quantities
  define('D_QUANTITY_DECIMALS',  $quantity_decimals);
  define('D_QUANTITY_DECIMALS_QUANTITY',  $quantity_decimals_quantity);
  define('D_QUANTITY_INTEGER_QUANTITY',  $quantity_integer_quantity);
  //  Texts
  define('D_MAX_CHARACTERS_DESCRIPTION',  $max_characters_description);
  define('D_MAX_CHARACTERS_TEXT',  $max_characters_text);
  define('D_MAX_CHARACTERS_INFO',  $max_characters_info);
  define('D_MAX_CHARACTERS_COMMENTS',  $max_characters_comments);

?>