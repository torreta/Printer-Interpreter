<?php

/*** (A) CHECK ***/
if (PHP_SAPI != "cli") {
  die("Esto solo funciona ejecutandose desde consola.");
}

include_once ("TfhkaPHP.php"); 

function translateTasa($tasa=""){
    // de momento tengo entendido 4 tipos de tasa
    // (falta copiar de manuak, pero en ejemplo tengo)// (!), ("), (#), ( )
   
    if($tasa == ""){
     //echo("valor vacio de tasa\n");
     return false;
    }

    switch ($tasa) {
      case "Exento":
        //echo "Exento\n";
        $comando = " ";
        break;
      case "Tasa 1":
        //echo "Tasa 1\n";
        $comando = "!";
        break;
      case "Tasa 2":
        //echo "Tasa 2\n";
        $comando = "\"";
        break;
      case "tasa 3":
        //echo "Tasa 3\n";
        $comando = "#";
        break;
      default:
       //echo "Tasa no reconocida\n";
       $comando = false;
       $tasas = ["\"","!","#"," "];
       $tasa = $tasas[array_rand($tasas, 1)];
       $comando = $tasa;
    }
   
   return  $comando;
  }
   
  // buscar expresion regular compatible con la mision de
  // verificar si un numero es valido
  // verificar si es valido sin decimales
  // verificar si es valido con comas
  // verificar si es valido con puntos
  function validador_numerico($value){
     return (preg_match ('~^((?:\+|-)?[0-9]+)$~' ,$value) == 1);
  }
   
  function padding_number_format($value="", $max_cifras="" ){

    // echo("valor precio\n" . $value . "\n");
    // echo("valor cifras\n" . $max_cifras . "\n");

    $cifras_padding = $max_cifras - strlen($value) ;
    // echo("padding cifras \n" . $cifras_padding . "\n");
    if( $cifras_padding < 0){echo "numero de cifras permitidas excedido"; }

    $padding ="";

    // construyo cuantos ceros falten para completar el padding 
    for ($i = 1; $i <= $cifras_padding; $i++) {
     $padding = $padding . "0";
    } 

    // echo(" padding y valor: \n" . $padding . $value ." \n");

     return $padding . $value;
   }

   function padding_decimal_format($value="", $max_cifras="" ){

   //  echo("valor precio\n" . $value . "\n");
   //  echo("valor cifras\n" . $max_cifras . "\n");

    $cifras_padding = $max_cifras - strlen($value) ;
   // echo("padding cifras \n" . $cifras_padding . "\n");
   if( $cifras_padding < 0){echo "numero de cifras decimales permitidas excedido"; }

    $padding ="";

    // construyo cuantos ceros falten para completar el padding 
    for ($i = 1; $i <= $cifras_padding; $i++) {
     $padding = $padding . "0";
    } 

    // echo(" padding y valor: \n" . $value . $padding ." \n");

     return  $value. $padding;
  }

  function translatePrecio( $precio = ""){
    //validaciones de tipo precio
    // Precio del ítem (8 enteros + 2 decimales)
    $enteros = "";   // 8 siempre, cualquier numero + relleno en ceros
    $decimales = ""; // 2 siempre, cualquier numero + relleno en ceros 

    // pico el numero en 2, quizas no se pique por ser un entero
    // $precio = "12.6";
   
    if($precio == ""){
     // echo("valor vacio de precio\n");
     return false;
    }

     // aqui va la funcion expresion regular validador de numeros
    if (is_numeric($precio) == false){
     // echo("valor invalido cifras\n" + $precio );
     return false;
    }else{
      // echo("valor numerico\n");
    }

    // se hace esto porque la cifra y los decimales en la traduccion no tienen
    // ningun tipo de marcacion, solo se asume que son los ultimos 2 digitos los decimales
 
    // separo la cifra en 2 pedazos, entero y decimal para poder evaluarlo aparte
    $cifras_separadas = explode(".",$precio); 

    // evaluo en la cantidad de pedazos en que se pico el numero, si es anormal se descarta
    $cant_cifras = count($cifras_separadas);
   
    //echo("cant cifras \n" . $cant_cifras."\n");

     // con solo parte entera tengo que agregar padding decimal
     // y tengo que completar lo que sea el numero entero a 8 digitos con padding
     // de ceros.

     switch ($cant_cifras) {
       case 1:
         // con solo parte entera tengo que agregar padding decimal
         //echo "solo numero sin decimales\n";
         //echo("valor entero\n ". $cifras_separadas[0] . "\n");
         $decimales = "00";
         $enteros = padding_number_format($cifras_separadas[0],8);
         //echo($enteros);
         break;
       case 2:
         // 
        //  echo "numero + decimales\n";
        //  echo("valor entero\n ". $cifras_separadas[0] . "\n");
        //  echo("valor decimal\n ". $cifras_separadas[1] . "\n");
     
         $enteros = padding_number_format($cifras_separadas[0],8);
         $decimales = padding_decimal_format($cifras_separadas[1],2);  

         break;
       default:
         //echo "formato de numero no reconocido\n";
         return false;
     }
   
     return  $enteros.$decimales;
  }
   
  function translateCantidad($cant = ""){
    //validaciones de cantidad
    // cantidad del ítem (5 enteros + 3 decimales)
    $enteros = "";   // 5 siempre, cualquier numero + relleno en ceros
    $decimales = ""; // 3 siempre, cualquier numero + relleno en ceros 

    // pico el numero en 2, quizas no se pique por ser un entero
    // $cant = "1250.955";
   
    if($cant == ""){
     //echo("valor vacio de cantidad\n");
     return false;
    }

     // aqui va la funcion expresion regular validador de numeros
    if (is_numeric($cant) == false){
     //echo("valor invalido cifras\n" + $cant );
     return false;
    }

    // se hace esto porque la cifra y los decimales en la traduccion no tienen
    // ningun tipo de marcacion, solo se asume que son los ultimos 3 digitos son decimales
 
    // separo la cifra en 2 pedazos, entero y decimal para poder evaluarlo aparte
    $cifras_separadas = explode(".",$cant); 

    // evaluo en la cantidad de pedazos en que se pico el numero, si es anormal se descarta
    $cant_cifras = count($cifras_separadas);
   
    //echo("cant cifras \n" . $cant_cifras."\n");

     // con solo parte entera tengo que agregar padding decimal
     // y tengo que completar lo que sea el numero entero a 5 digitos con padding
     // de ceros.

     switch ($cant_cifras) {
       case 1:
         // con solo parte entera tengo que agregar padding decimal
        //  echo "solo numero sin decimales\n";
        //  echo("valor entero\n ". $cifras_separadas[0] . "\n");
         $decimales = "000";
         $enteros = padding_number_format($cifras_separadas[0],5);
         //echo($enteros);
         break;
       case 2:
         // 
        //  echo "numero + decimales\n";
        //  echo("valor entero\n ". $cifras_separadas[0] . "\n");
        //  echo("valor decimal\n ". $cifras_separadas[1] . "\n");
     
         $enteros = padding_number_format($cifras_separadas[0],5);
         $decimales = padding_decimal_format($cifras_separadas[1],3);  

         break;
       default:
         //echo "formato de numero no reconocido cantidad\n";
         return false;
     }
   
     return  $enteros.$decimales;

  }
   
  function translateDescription($desc = ""){
 
     $max_caracteres = 20; //definido en el manual

    if($desc == ""){
     //echo("Descripcion Vacia\n");
     return false;
    }

     $comando = substr($desc,0,$max_caracteres);

   return  $comando;
  }
   
   
  function translateLine( $tasa="", $precio = "", $cant = "", $desc = "",$tipo_doc=""){
   
   $comando = translateTasa($tasa, $tipo_doc) .translatePrecio($precio) . translateCantidad($cant) .translateDescription($desc);
   
    //echo "\n\nComando Final\n"; 
   
   return  $comando;
  }


/*** (B) SETTINGS ***/
// Database settings - change these to your own
define('DB_HOST', 'localhost');
define('DB_NAME', 'pos_development');
define('DB_CHARSET', 'utf8');
define('DB_USER', 'root');
define('DB_PASSWORD', null);

define('PRINTER_ID', 1); //la impresora en uso

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
define('LOOP_CYCLE', 6); // Loop every 60 secs

/*** (C) CONNECT DATABASE ***/
  $servername = "localhost";
  $username = "root";
  $password = null;
  $dbname = "pos_development";

  // Create connection
  $conn = new mysqli($servername, $username, $password, $dbname);

  // Check connection
  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }

/*** (D) LOOP CHECK ***/
while (true) {

  // Check for invoices current, if so, then
  $facturas = "SELECT * from dbo_printer_current WHERE printer_id = ".PRINTER_ID.";";
  $result = $conn->query($facturas);

  // counter for translation
  $factura = array();
  $index_counter = 0;

  echo "\n";
  echo "items";
  var_dump( $result );
  var_dump( $result->num_rows );
  var_dump( $result->num_rows > 0);
  echo "\n";

  // (0) verifico si hay facturas en la tabla current (1)

  // (1) (true) de haber tomo el documento y lo imprimo (...desarrollar ++) (3)

  // (1) (false) de no haber, busco en pendientes (2)

  // (2) (true) de haber en pendientes (para la impresora especificada), tomo el siguiente en orden FIFO de la cola
  // ... y lo coloco en current ese invoice. (END)

  // (2) (false) de no existir pendientes, no hay que hacer mas nada, solo esperar (Sleep) (END)

  // (3) (true) verifico el mensaje del controlador al imprimir, (condiciones de parseo), si todo sale exitoso
  // ... se toma el individuo en current y se copia a history. 
  // ... se borra de current
  // ... se sobrescribe el mensaje para la impresora de mensajes 
  // ... se coloca el mensaje en la tabla log de mensajes (END)

  // (3) (false)  verifico el mensaje del controlador al imprimir, (condiciones de parseo), si sale un error
  // ... se mantiene la factura en current (sin cambios)
  // ... se sobrescribe el mensaje para la impresora de mensajes, indicando que hay un error
  // ... se coloca el mensaje en la tabla log de mensajes (END)


  // PASITO A PASITO
  // (0) verifico si hay facturas en la tabla current (1)

  // (1) (true) de haber tomo el documento y lo imprimo (...desarrollar ++) (3)

  // (1) (false) de no haber, busco en pendientes (2)

  // (2) (true) de haber en pendientes (para la impresora especificada), tomo el siguiente en orden FIFO de la cola
  // ... y lo coloco en current ese invoice. (END)

  // (2) (false) de no existir pendientes, no hay que hacer mas nada, solo esperar (Sleep) (END)

  // (3) (true) verifico el mensaje del controlador al imprimir, (condiciones de parseo), si todo sale exitoso
  // ... se toma el individuo en current y se copia a history. 
  // ... se borra de current
  // ... se sobrescribe el mensaje para la impresora de mensajes 
  // ... se coloca el mensaje en la tabla log de mensajes (END)

  // (3) (false)  verifico el mensaje del controlador al imprimir, (condiciones de parseo), si sale un error
  // ... se mantiene la factura en current (sin cambios)
  // ... se sobrescribe el mensaje para la impresora de mensajes, indicando que hay un error
  // ... se coloca el mensaje en la tabla log de mensajes (END)



  // if (  $result->num_rows > 0) { //significa que hay almenos 1 factura

  //   // output data of each row
  //  /*  while($row = $result->fetch_assoc()) {
  //     echo "\n";
  //     echo "factura de id: " . $row["invoice_id"]." esta imprimiendo acturalmente;";
  //     echo "\n";
  //     // id de la factura en cuestion
  //     $invoice_id = $row["invoice_id"];

  //     $items_factura = "SELECT * from dbo_printer_current WHERE printer_id = ".PRINTER_ID.";";

  //     $result = $conn->query($items_factura); */


  //     // if (count($items_factura) > 0) {
  //     //   // output data of each row
  //     //   while($row = $items_factura->fetch_assoc()) {
  //     //     echo "\n";
  //     //     echo "price: " . $row["price"]. " - quantity: " . $row["quantity"]. ", description " . $row["description"]. "<br>";
  //     //     echo "\n";

  //     //     // $factura[$index_counter] = "price: " . $row["price"]. " - quantity: " . $row["quantity"]. ", description " . $row["description"]. "<br> \n";
  //     //     $factura[$index_counter] = translateLine("X",$row["price"],$row["quantity"],$row["description"])."\n";
  //     //     $index_counter++;
  //     //   }

  //     //   //cierre de factura
  //     //   $factura[$index_counter] = "101";
  //     //       echo "\n";
  //     //       echo "factura generada";
  //     //       echo "\n";

  //     //       var_dump( $factura); 

  //     //     //   $file = "Factura.txt";  
  //     //     //     $fp = fopen($file, "w+");
  //     //     //     $write = fputs($fp, "");
                              
  //     //     //   foreach($factura as $campo => $cmd)
  //     //     //   {
  //     //     //     $write = fputs($fp, $cmd);
  //     //     //   }
                              
  //     //     // fclose($fp); 

  //     //     //revisar resultados de dispositivo
  //     //     // $out =  $itObj->SendFileCmd($file);
  //     //     // var_dump($out);


  //     // } else {
  //     //   echo "0 results for items";
  //     //   // log para algo serio
  //     // }
      
  //   }

  //     //cerrando db
  //      $conn->close();

  
  //   // while($row = $result->fetch_assoc()) {
  //   //   echo "price: " . $row["price"]. " - quantity: " . $row["quantity"]. ", description " . $row["description"]. "<br>";
  //   //   echo "\n";

  //   //   // $factura[$index_counter] = "price: " . $row["price"]. " - quantity: " . $row["quantity"]. ", description " . $row["description"]. "<br> \n";
  //   //   $factura[$index_counter] = translateLine("X",$row["price"],$row["quantity"],$row["description"])."\n";
  //   //   $index_counter++;
  //   // }

  //   // if ($result->num_rows > 0) {
  //   //   // output data of each row
  //   //   while($row = $result->fetch_assoc()) {
  //   //     echo "price: " . $row["price"]. " - quantity: " . $row["quantity"]. ", description " . $row["description"]. "<br>";
  //   //     echo "\n";

  //   //     // $factura[$index_counter] = "price: " . $row["price"]. " - quantity: " . $row["quantity"]. ", description " . $row["description"]. "<br> \n";
  //   //     $factura[$index_counter] = translateLine("X",$row["price"],$row["quantity"],$row["description"])."\n";
  //   //     $index_counter++;
  //   //   }

  //   //   //cierre de factura
  //   //   $factura[$index_counter] = "101";

  //   // } else {
  //   //   echo "0 results";
  //   // }



  //   // $_query = $_PDO->prepare("
  //   //       SELECT
  //   //         dbo_administration_invoices_items.id,
  //   //         dbo_administration_invoices_items.invoice_id,
  //   //         dbo_administration_invoices_items.price,
  //   //         dbo_administration_invoices_items.quantity, 
  //   //         dbo_administration_invoices_items.tax_id,
  //   //         dbo_config_taxes.percentage,
  //   //         dbo_config_taxes.observation,
  //   //         dbo_administration_invoices_items.exchange_rate_id,
  //   //         dbo_config_exchange_rates.exchange_rate,
  //   //         dbo_config_currencies.abbreviation,
  //   //         dbo_config_currencies.`name`,
  //   //         dbo_storage_products.`code`,
  //   //         dbo_storage_products.description
  //   //       FROM `dbo_administration_invoices_items`
  //   //       join dbo_config_taxes on dbo_administration_invoices_items.tax_id = dbo_config_taxes.id
  //   //       join dbo_config_exchange_rates on dbo_administration_invoices_items.exchange_rate_id = dbo_config_exchange_rates.id
  //   //       join dbo_config_currencies on dbo_config_exchange_rates.currency_id = dbo_config_currencies.id
  //   //       join dbo_storage_products on dbo_administration_invoices_items.product_id = dbo_storage_products.id
  //   //       WHERE 	dbo_administration_invoices_items.invoice_id = " .."
  //   //       ;
  //   //     ");
  //   // $_query->execute();
  //   // $facturas = $_query->fetchAll();





  //   // while($row = $result->fetch_assoc()) {
  //   //   echo "price: " . $row["price"]. " - quantity: " . $row["quantity"]. ", description " . $row["description"]. "<br>";
  //   //   echo "\n";

  //   //   // $factura[$index_counter] = "price: " . $row["price"]. " - quantity: " . $row["quantity"]. ", description " . $row["description"]. "<br> \n";
  //   //   $factura[$index_counter] = translateLine("X",$row["price"],$row["quantity"],$row["description"])."\n";
  //   //   $index_counter++;
  //   // }

  //   // //cierre de factura
  //   // $factura[$index_counter] = "101";

  // } else {
  //   echo "nothing currently printing, cheking pending";

  //   //busco pendientes

  // }

  // //     var_dump( $factura); 

  // //     $file = "Factura.txt";  
  // //       $fp = fopen($file, "w+");
  // //       $write = fputs($fp, "");
                        
  // //     foreach($factura as $campo => $cmd)
  // //     {
  // //       $write = fputs($fp, $cmd);
  // //     }
                        
  // //   fclose($fp); 

  //   //revisar resultados de dispositivo
  //   // $out =  $itObj->SendFileCmd($file);
  //   // var_dump($out);


  // Sleep
  sleep(LOOP_CYCLE);
}

    // cd C:\xampp\htdocs\Printer-Interpreter && php daemon.php
    // php daemon.php

?>