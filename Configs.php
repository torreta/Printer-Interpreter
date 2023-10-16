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
  define('PRINTER_ID', 7); //la impresora en uso (numerada en BD)

  // Cycle (how ofter the daemon check the database)
  define('LOOP_CYCLE', 1); // Ciclo determinado en segundos, en este caso cada segundo

  // Error and reporting (file for log)(must exists or fails)
  ini_set("display_errors", 1);
  error_reporting(E_ALL & ~E_NOTICE);
  define('LOG_KEEP', true);
  define('LOG_FILE', 'daemon.log');

  // VERBOSE EXECUTION
  define('VERBOSE', false);
  
  function addlog ($message="") {
    error_log( "[" . date("Y-m-d H:i:s") . "] " . $message . PHP_EOL, 3, LOG_FILE );
  }

  // PRINER MODEL (a way to check if we can configure these variables by default)
  $Printer_model = "HK80_VE_IGTF"; // <---- Check switch to set this

  // IGTF 3% ACTIVE?
  $IGTF = true;


  // DEFAULT VALUES
  // you have to check this on the command manual from printer
  // prices
  $default_price_decimals = "00";
  $default_price_decimals_quantity = 2;
  $default_price_integer_quantity = 8;

  // Payments
  $default_payments_decimals = "00";
  $default_payments_decimals_quantity = 2;
  $default_payments_integer_quantity = 10;
    
  // quantity
  $default_quantity_decimals = "000";
  $default_quantity_decimals_quantity = 3;
  $default_quantity_integer_quantity = 5;

  // Texts
  $default_max_characters_description = 20;
  $default_max_characters_text = 40;
  $default_max_characters_info = 40;
  $default_max_characters_comments = 40;

  // discounts
  $default_discounts_decimals = "00";
  $default_discounts_decimals_quantity = 2;
  $default_discounts_integer_quantity = 7;

  // MODEL CASES
  switch ($Printer_model) {
    case "EMULATOR":
      // prices
      $price_decimals = $default_price_decimals;
      $price_decimals_quantity = $default_price_decimals_quantity;
      $price_integer_quantity = $default_price_integer_quantity;
      // Payments
      $payments_decimals =  $default_payments_decimals;
      $payments_decimals_quantity = $default_payments_decimals_quantity;
      $payments_integer_quantity =  $default_payments_integer_quantity;
      // quantity
      $quantity_decimals = $default_quantity_decimals;
      $quantity_decimals_quantity = $default_quantity_decimals_quantity;
      $quantity_integer_quantity = $default_quantity_integer_quantity;
      // texts
      $max_characters_description = $default_max_characters_description;
      $max_characters_text = $default_max_characters_text;
      $max_characters_info = $default_max_characters_info;
      $max_characters_comments = $default_max_characters_comments;
      // discounts
      $discounts_decimals = $default_discounts_decimals;
      $discounts_decimals_quantity = $default_discounts_decimals_quantity;
      $discounts_integer_quantity =  $default_discounts_integer_quantity;
    break;
    case "SRP-350":
      // prices
      $price_decimals = "00";
      $price_decimals_quantity = 2;
      $price_integer_quantity = 8;
      // Payments
      $payments_decimals = "00";
      $payments_decimals_quantity = 2;
      $payments_integer_quantity = 10;
      // quantity
      $quantity_decimals = "000";
      $quantity_decimals_quantity = 3;
      $quantity_integer_quantity = 5;
      // texts
      $max_characters_description = 20;
      $max_characters_text = 40;
      $max_characters_info = 40;
      $max_characters_comments = 40;
      // discounts
      $discounts_decimals = "00";
      $discounts_decimals_quantity = 2;
      $discounts_integer_quantity = 7;
    break;
    case "BIXOLON":
      // prices
      $price_decimals = "00";
      $price_decimals_quantity = 2;
      $price_integer_quantity = 8;
      // Payments
      $payments_decimals = "00";
      $payments_decimals_quantity = 2;
      $payments_integer_quantity = 10;
      // quantity
      $quantity_decimals = "000";
      $quantity_decimals_quantity = 3;
      $quantity_integer_quantity = 5;
      // texts
      $max_characters_description = 20;
      $max_characters_text = 40;
      $max_characters_info = 40;
      $max_characters_comments = 40;
      // discounts
      $discounts_decimals = "00";
      $discounts_decimals_quantity = 2;
      $discounts_integer_quantity = 7;
    break;
    case "HK80_VE":
      // prices
      $price_decimals = "00";
      $price_decimals_quantity = 2;
      $price_integer_quantity = 14;
      // Payments
      $payments_decimals = "00";
      $payments_decimals_quantity = 2;
      $payments_integer_quantity = 15;
      // quantity
      $quantity_decimals = "000";
      $quantity_decimals_quantity = 3;
      $quantity_integer_quantity = 14;
      // texts
      $max_characters_description = 20;
      $max_characters_text = 40;
      $max_characters_info = 40;
      $max_characters_comments = 40;
      // discounts
      $discounts_decimals = "00";
      $discounts_decimals_quantity = 2;
      $discounts_integer_quantity = 15;
    break;
    case "HK80_VE_IGTF":
      // prices
      $price_decimals = "00";
      $price_decimals_quantity = 2;
      $price_integer_quantity = 8;
      // Payments
      $payments_decimals = "00";
      $payments_decimals_quantity = 2;
      $payments_integer_quantity = 10;
      // quantity
      $quantity_decimals = "000";
      $quantity_decimals_quantity = 3;
      $quantity_integer_quantity = 5;
      // texts
      $max_characters_description = 20;
      $max_characters_text = 40;
      $max_characters_info = 40;
      $max_characters_comments = 40;
      // discounts
      $discounts_decimals = "00";
      $discounts_decimals_quantity = 2;
      $discounts_integer_quantity = 7;
    break;
    case "LOSCOCOS": //CUSTOM
      // prices
      $price_decimals = "00";
      $price_decimals_quantity = 2;
      $price_integer_quantity = 14;
      // Payments
      $payments_decimals = "00";
      $payments_decimals_quantity = 2;
      $payments_integer_quantity = 15;
      // quantity
      $quantity_decimals = "000";
      $quantity_decimals_quantity = 3;
      $quantity_integer_quantity = 14;
      // texts
      $max_characters_description = 20;
      $max_characters_text = 40;
      $max_characters_info = 40;
      $max_characters_comments = 40;
      // discounts
      $discounts_decimals = "00";
      $discounts_decimals_quantity = 2;
      $discounts_integer_quantity = 15;
    break;

    default: // undetermined printer
      // prices
      $price_decimals = $default_price_decimals;
      $price_decimals_quantity = $default_price_decimals_quantity;
      $price_integer_quantity = $default_price_integer_quantity;
      // Payments
      $payments_decimals =  $default_payments_decimals;
      $payments_decimals_quantity = $default_payments_decimals_quantity;
      $payments_integer_quantity =  $default_payments_integer_quantity;
      // quantity
      $quantity_decimals = $default_quantity_decimals;
      $quantity_decimals_quantity = $default_quantity_decimals_quantity;
      $quantity_integer_quantity = $default_quantity_integer_quantity;
      // texts
      $max_characters_description = $default_max_characters_description;
      $max_characters_text = $default_max_characters_text;
      $max_characters_info = $default_max_characters_info;
      $max_characters_comments = $default_max_characters_comments;
      // discounts
      $discounts_decimals = $default_discounts_decimals;
      $discounts_decimals_quantity = $default_discounts_decimals_quantity;
      $discounts_integer_quantity =  $default_discounts_integer_quantity;

    break;
  }

  //  Prices
  define('D_PRICE_DECIMALS',  $price_decimals);
  define('D_PRICE_DECIMALS_QUANTITY',  $price_decimals_quantity);
  define('D_PRICE_INTEGER_QUANTITY',  $price_integer_quantity);
  //  Payments
  define('D_PAYMENTS_DECIMALS',  $payments_decimals);
  define('D_PAYMENTS_DECIMALS_QUANTITY',  $payments_decimals_quantity);
  define('D_PAYMENTS_INTEGER_QUANTITY',  $payments_integer_quantity);
  //  Quantities
  define('D_QUANTITY_DECIMALS',  $quantity_decimals);
  define('D_QUANTITY_DECIMALS_QUANTITY',  $quantity_decimals_quantity);
  define('D_QUANTITY_INTEGER_QUANTITY',  $quantity_integer_quantity);
  //  Texts
  define('D_MAX_CHARACTERS_DESCRIPTION',  $max_characters_description);
  define('D_MAX_CHARACTERS_TEXT',  $max_characters_text);
  define('D_MAX_CHARACTERS_INFO',  $max_characters_info);
  define('D_MAX_CHARACTERS_COMMENTS',  $max_characters_comments);
  // discounts
  define('D_DISCOUNTS_DECIMALS',  $discounts_decimals);
  define('D_DISCOUNTS_DECIMALS_QUANTITY',  $discounts_decimals_quantity);
  define('D_DISCOUNTS_INTEGER_QUANTITY',  $discounts_integer_quantity);

  // IGTF
  define('D_IMPUESTO_GRANDES_TRANSACCIONES_FISCALES', $IGTF);
  define('D_IGTF', $IGTF);

  // PRINTER MODE (EN CASO DE QUE QUIERA LEER EL VALOR EN OTRO SITIO)
  define('D_PRINTER_MODEL', $Printer_model);

?>