<?php

/*** (A) CHECK ***/
if (PHP_SAPI != "cli") {
  die("Esto solo funciona ejecutandose desde consola.");
}

include_once ("TfhkaPHP.php"); 
include_once ("interpreter.php"); 

$itObj = new Tfhka();

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

  // cerrando db (FALTA COLOCARLO EN UN MEJOR LUGAR)
  // $conn->close();


/*** (D) LOOP CHECK ***/
while (true) {

  // (0) verifico si hay documentos en la tabla current (1)
  
  // (1) (true) de haber tomo el documento y lo imprimo (...desarrollar ++) (3)

  // (1) (false) de no haber, busco en pendientes (2)

  // (2) (true) de haber en pendientes (para la impresora especificada), tomo el siguiente en orden FIFO de la cola
  // ... y lo coloco en current ese invoice.
  // ... y lo borro el seleccionado de pendientes  (END)

  // (2) (false) de no existir pendientes, no hay que hacer mas nada, solo esperar (Sleep) (END)

  // (3) (true) verifico el mensaje del controlador al imprimir, (condiciones de parseo), si todo sale exitoso
  // ... se toma el individuo en current y se copia a history. 
  // ... se borra de current
  // ... se sobrescribe el mensaje para la impresora de mensajes 
  // ... se coloca el mensaje en la tabla log de mensajes (END)

  // (3) (false)  verifico el mensaje del controlador al imprimir, (condiciones de parseo), si sale un error
  // ... se mantiene la documento en current (sin cambios)
  // ... se sobrescribe el mensaje para la impresora de mensajes, indicando que hay un error
  // ... se coloca el mensaje en la tabla log de mensajes (END)


  // PASITO A PASITO
  // (0) verifico si hay documentos en la tabla current (1)
  //.. en esto particular para la impresora en particular.
  $query_documentos_imprimiendo = "SELECT * from dbo_printer_current WHERE printer_id = ".PRINTER_ID.";";
  $documentos_imprimiendo = null;
  $documentos_imprimiendo = $conn->query($query_documentos_imprimiendo);

  // contador para la traduccion (quizas mal puesto aqui)
  $documento = array();
  $index_counter = 0;

  // (1) (true) de haber tomo el documento y lo imprimo (...desarrollar ++) (3)
  if ($documentos_imprimiendo->num_rows > 0) { // aqui chequeo si tengo almenos 1

    // solo debe haber una por impresora en la tabla current
    $documento_imprimiendo = null;
    $documento_imprimiendo = $documentos_imprimiendo->fetch_assoc();
    // var_dump($documento_imprimiendo);

    // segun el tipo de documento (solo facturas de momentos)
    switch ($documento_imprimiendo["document_type_id"]) {
      case "1": // Factura (unico caso de momento)
        // tomo el id de la factura
        $invoice_id = null;
        $invoice_id = $documento_imprimiendo["document_id"];
        
        // inicializo una instancia de interprete para el tipo de doc.
        // ...(hago una instancia del interprete del tipo de doc)
        $interpreter = new interpreter();

        // detalles de documento
        $query_info_factura = "SELECT * FROM dbo_administration_invoices WHERE id = ".$invoice_id.";";
        $info_factura = null;
        $info_factura = $conn->query($query_info_factura);

        if ($info_factura->num_rows == 0) { die("factura con ese id no existe"); }

        $factura_actual = $info_factura->fetch_assoc();

        $numero_factura = $factura_actual["invoice_number"];
        $nombre_cajero = $documento_imprimiendo["cashier_name"];

        echo "\n";
        echo "el documento a imprimir es la factura " . $numero_factura .", por cajero ". $nombre_cajero. "\n ";
        echo "\n";

        $query_items_factura = "
          SELECT
            dbo_administration_invoices_items.id,
            dbo_administration_invoices_items.invoice_id,
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
          join dbo_storage_products on dbo_administration_invoices_items.product_id = dbo_storage_products.id
          WHERE 	dbo_administration_invoices_items.invoice_id = " .$invoice_id.";
        ";

        $items_factura = null;
        $items_factura = $conn->query($query_items_factura);

          if ($items_factura->num_rows > 0) {
            // counter for translation
            $factura_en_contruccion = array();
            $index_counter = 0;

            // consultar informacion fiscal de la factura antes de armarla
            // .............. (FALTA)


            // output data of each row
            while($item = $items_factura->fetch_assoc()) {
              // echo "\n";
              // echo "price: " . $item["price"]. " - quantity: " . $item["quantity"]. ", description " . $item["description"];
              // echo "\n";

              // proximamente al interpreter
              // .. el tax rate, deberia pasarse en texto (FALTA)
              $factura_en_contruccion[$index_counter] = $interpreter->translateLine("X",$item["price"],$item["quantity"],$item["description"])."\n";
              $index_counter++;
            }

            //cierre de factura
            $factura_en_contruccion[$index_counter] = "101";

            echo "\n";
            var_dump($factura_en_contruccion) ;
            echo "\n";
            
            // escribo en un archivo el contenido de la factura en contruccion
            // ... puedo pasar esto a un controlador aparte (FALTA)
            $file = "Factura".$numero_factura.".txt";	
              $fp = fopen($file, "w+");
              $write = fputs($fp, "");
                              
            foreach($factura_en_contruccion as $campo => $cmd)
            {
              $write = fputs($fp, $cmd);
            }
            
            //cierro dicho archivo
            fclose($fp);

            // enviarlo a imprimir (PROBARLO Y EJECUTARLO IMPRESORA)(FALTA)
            // ... enviar a imprimir
            $respuesta_impresora =  $itObj->SendFileCmd($file);

            // ... para probar voy a decir que la impresora dijo algo 
            // $respuestas_impresora = ["true","false"];
            //$respuesta_impresora = $respuestas_impresora[array_rand($respuestas_impresora, 1)];

            // interpretar la respuesta de la impresora
            $respuesta_impresora = $interpreter->respuesta_impresora($respuesta_impresora);

            // (3) (true) verifico el mensaje del controlador al imprimir, (condiciones de parseo), si todo sale exitoso
            if($respuesta_impresora == "true"){
              // ... se toma el individuo en current y se copia a history.
              $query_a_historico = 
                "INSERT INTO dbo_printer_history(
                      document_type_id, 
                      document_id, 
                      printer_id, 
                      user_id, 
                      cashier_name) 
                  VALUES ("
                  .$documento_imprimiendo["document_type_id"].","
                  .$documento_imprimiendo["document_id"].", "
                  .$documento_imprimiendo["printer_id"].", "
                  .$documento_imprimiendo["user_id"].", '"
                  .$documento_imprimiendo["cashier_name"]."');";
              
                // puedes validar el query aca
                // echo ( $query_a_historico );

                $insertar_registro = $conn->prepare($query_a_historico);

              if ($insertar_registro->execute()) {
                echo "Se ha registrado la factura en el historial \n";
              } else {
                echo "(al insertar a historial) Error: " . $sql . "\n" . mysqli_error($conn);
              }

              // ... se borra de current
              $query_delete_current = 
                " DELETE 
                    FROM dbo_printer_current 
                  WHERE
                    document_type_id = ".$documento_imprimiendo["document_type_id"]." && 
                    document_id = ".$documento_imprimiendo["document_id"]." && 
                    printer_id = ".$documento_imprimiendo["printer_id"].";
                  ";
              
                // puedes validar el query aca
                // echo ( $query_delete_current );

                $borrar_current_registro = $conn->prepare($query_delete_current);

              if ($borrar_current_registro->execute()) {
                echo "Se ha borrado la factura de las imprimiendo, por haber sido completada. \n";
              } else {
                echo "(al borrar de current) Error: " . $sql . "\n" . mysqli_error($conn);
              }

              // ... se sobrescribe el mensaje para la impresora de mensajes 
              $mensaje_al_log = "la factura " . $numero_factura .", por cajero ". $nombre_cajero. "ha impreso con exito.";

              $query_update_message = 
              "INSERT OR UPDATE INTO dbo_printer_messages(
                    id, 
                    message, 
                    printer_id, 
                    user_id, 
                    cashier_name) 
                VALUES ("
                .$documento_imprimiendo["printer_id"].","
                .$mensaje_al_log.", "
                .$documento_imprimiendo["printer_id"].", "
                .$documento_imprimiendo["user_id"].", '"
                .$documento_imprimiendo["cashier_name"]."');";

              // puedes validar el query aca
              // echo ( $query_update_message );

              $actualizar_mensaje_impresora = $conn->prepare($query_update_message);

            if ($actualizar_mensaje_impresora->execute()) {
              echo "se ha actualizado el mensaje de la impresora. \n";
            } else {
              echo "(al actualizar mensaje de la impresora) Error: " . $sql . "\n" . mysqli_error($conn);
            }
            
            // ... se coloca el mensaje en la tabla log de mensajes (END)

            }else{
              // (3) (false)  verifico el mensaje del controlador al imprimir, (condiciones de parseo), si sale un error
              // ... se mantiene la factura en current (sin cambios)
              // ... se sobrescribe el mensaje para la impresora de mensajes, indicando que hay un error
              // ... se coloca el mensaje en la tabla log de mensajes (END)
              echo "la impresora fallo... (hay que colocar los errores en log)\n";
              // ... busco en checkprinter cual puede ser la razon del error.

            }

          } else {
            echo "no hay items asociados a esa factura \n";
          }

          break;
        case "2":// Devolucion
          break;
        case "3":// Nota de Entrega
          break;
        case "4":// Nota no Fiscal
          break;
        default: // Documento indeterminado
          die("Documento indeterminado"); 
      }

    
  // (1) (false) de no haber, busco en pendientes (2)
  }else{ 
    // ... reviso que no haya documentos en pendientes
    $query_documentos_pendientes = "SELECT * from dbo_printer_pending WHERE printer_id = ".PRINTER_ID.";";
    $documentos_pendientes = null;
    $documentos_pendientes = $conn->query($query_documentos_pendientes);
  
     // (2) (true) de haber en pendientes (para la impresora especificada), 
     // ...tomo el siguiente en orden FIFO de la cola
    if ($documentos_pendientes->num_rows > 0) {
    
      // datos de la factura pendiente
      $documento_pendiente = $documentos_pendientes->fetch_assoc();

      // ... y lo coloco en current ese invoice.
      $query_a_imprimiendo = 
        "INSERT INTO dbo_printer_current(
              document_type_id, 
              document_id, 
              printer_id, 
              user_id, 
              cashier_name) 
          VALUES ("
          .$documento_pendiente["document_type_id"].","
          .$documento_pendiente["document_id"].", "
          .$documento_pendiente["printer_id"].", "
          .$documento_pendiente["user_id"].", '"
          .$documento_pendiente["cashier_name"]."');";
      
        // puedes validar el query aca
        // echo ( $query_a_imprimiendo );

        $insertar_registro = $conn->prepare($query_a_imprimiendo);

      if ($insertar_registro->execute()) {
        echo "Se ha registrado una factura a imprimiendo \n";
      } else {
        echo "(al insertar a imrpimiendo) Error: " . $sql . "\n" . mysqli_error($conn);
      }

      // ... se borra de dicha factura de pendiente (END)
      $query_delete_pending = 
        " DELETE 
            FROM dbo_printer_pending
          WHERE
            document_type_id = ".$documento_pendiente["document_type_id"]." && 
            document_id = ".$documento_pendiente["document_id"]." &&
            id = ".$documento_pendiente["id"]." &&  
            printer_id = ".$documento_pendiente["printer_id"].";
          ";
      
        // puedes validar el query aca
        // echo ( $query_delete_pending );

        $borrar_pendiente_registro = $conn->prepare($query_delete_pending);

      if ($borrar_pendiente_registro->execute()) {
        echo "Se ha borrado la factura pendientes, por haber sido llevada a imprimir. \n";
      } else {
        echo "(al borrar de pendientes) Error: " . $sql . "\n" . mysqli_error($conn);
      }

    }else{
      // (2) (false) de no existir pendientes, no hay que hacer mas nada, solo esperar (Sleep) (END)
      echo("no hay documentos en pendientes, me tomo una siesta. \n");
    }

  }


  // Sleep
  sleep(LOOP_CYCLE);
}

    // cd C:\xampp\htdocs\Printer-Interpreter && php daemon.php
    // php daemon.php
    // cd  d:\xamp\htdocs\Printer-Interpreter && php daemon.php
    // D:\xamp\htdocs\Printer-Interpreter
?>