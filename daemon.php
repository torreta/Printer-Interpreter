<?php

/*** (A) CHECK ***/
if (PHP_SAPI != "cli") {
  die("Esto solo funciona ejecutandose desde consola.");
}

include_once ("TfhkaPHP.php"); 

/*** (B) SETTINGS ***/
// Database settings - change these to your own
define('DB_HOST', 'localhost');
define('DB_NAME', 'pos_development');
define('DB_CHARSET', 'utf8');
define('DB_USER', 'root');
define('DB_PASSWORD', null);

// $servername = "localhost";
// $username = "root";
// $password = null;
// $dbname = "pos_development";

// Error and reporting
ini_set("display_errors", 1);
error_reporting(E_ALL & ~E_NOTICE);
define('LOG_KEEP', true);
define('LOG_FILE', 'daemon.log');
function addlog ($message="") {
  error_log(
    "[" . date("Y-m-d H:i:s") . "] " . $message . PHP_EOL,
    3, LOG_FILE
  );
}

// Cycle
define('LOOP_CYCLE', 4); // Loop every 60 secs
define('EMAIL_ADMIN', "john@doe.com");

/*** (C) CONNECT DATABASE ***/
try {
  $_PDO = new PDO(
    "mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET . ";dbname=" . DB_NAME, 
    DB_USER, DB_PASSWORD, [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO::ATTR_EMULATE_PREPARES => false,
      PDO::ATTR_PERSISTENT => true
    ]
  );
}
catch (Exception $ex) {
  if (LOG_KEEP) { addlog($ex->getMessage()); }
  die("Error connecting to the database");
}

/*** (D) LOOP CHECK ***/
while (true) {
  // Check for new orders
  $_STMT = $_PDO->prepare("
        SELECT
        dbo_administration_invoices_items.id,
        dbo_administration_invoices_items.price,
        dbo_administration_invoices_items.quantity, 
        dbo_administration_invoices_items.tax_id,
        dbo_config_taxes.percentage,
        dbo_config_taxes.observation,
        dbo_administration_invoices_items.exchange_rate_id,
        dbo_config_exchange_rates.exchange_rate,
        dbo_config_currencies.abbreviation,
        dbo_config_currencies.`name`,
        dbo_storage_products.`code`,
        dbo_storage_products.description
      FROM `dbo_administration_invoices_items`
      join dbo_config_taxes on dbo_administration_invoices_items.tax_id = dbo_config_taxes.id
      join dbo_config_exchange_rates on dbo_administration_invoices_items.exchange_rate_id = dbo_config_exchange_rates.id
      join dbo_config_currencies on dbo_config_exchange_rates.currency_id = dbo_config_currencies.id
      join dbo_storage_products on dbo_administration_invoices_items.product_id = dbo_storage_products.id;
  ");
  $_STMT->execute();
  $orders = $_STMT->fetchAll();

  $factura = array();
  $index_counter = 0;


  // Email notification to admin
  // if (count($orders)>0) {
  //   $email = "Orders received<br>";
  //   foreach ($orders as $o) {
  //     $email .= $o["order_name"] . "<br>";
  //   }
  //   if (@mail(EMAIL_ADMIN, "Orders received", $email)) {
  //     $_STMT = $_PDO->prepare("UPDATE `orders` SET `order_status`=2 WHERE `order_status`=1");
  //     $_STMT->execute();
  //   } else {
  //     if (LOG_KEEP) { addlog("Email notification failed!"); }
  //   }
  // }

  if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
      echo "price: " . $row["price"]. " - quantity: " . $row["quantity"]. ", description " . $row["description"]. "<br>";
      echo "\n";

      // $factura[$index_counter] = "price: " . $row["price"]. " - quantity: " . $row["quantity"]. ", description " . $row["description"]. "<br> \n";
      $factura[$index_counter] = translateLine("X",$row["price"],$row["quantity"],$row["description"])."\n";
      $index_counter++;
    }

    //cierre de factura
    $factura[$index_counter] = "101";

    // $factura = array(
    //   -5 => "iF*0000001\n",//factura asociadaj
    //   -4 => "iI*Z4A1234567\n",// numero de control de esa factura
    //   -3 => "iD*18-01-2014\n",//fecha factura dia especifico
    //   -2 => "iS*Pedro Mendez\n", // mombre persona
    //   -1 => "iR*12.345.678\n", // rif
    //    0 => "!000001000000001000Harina\n",
    //    1 => "!000001000000001000Jamon\n",
    //    2 => " 000001000000001000caracteres especiale\n",
    //    3 => "#000001000000001000Caja de Whisky\n",
    //    4 => "101"
    //   );
    
  } else {
    echo "0 results";
  }

      var_dump( $factura); 

      $file = "Factura.txt";  
        $fp = fopen($file, "w+");
        $write = fputs($fp, "");
                        
      foreach($factura as $campo => $cmd)
      {
        $write = fputs($fp, $cmd);
      }
                        
    fclose($fp); 

    $out =  $itObj->SendFileCmd($file);

    var_dump($out);



  // Sleep
  sleep(LOOP_CYCLE);
}
?>