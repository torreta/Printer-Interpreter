<?php

/*** (A) CHECK ***/
if (PHP_SAPI != "cli") {
  die("Este daemonio solo funciona ejecutandose desde consola. de momento no muestro errores en el browser.");
}

include_once ("DatabaseBridge.php"); 
include_once ("invoiceHandler.php"); 
include_once ("creditnoteHandler.php"); 
include_once ("debitnoteHandler.php"); 

include_once ("Utils.php"); 

$DatabaseBridge =  new DatabaseBridge();
$Utils =  new Utils();

/*** (B) SETTINGS ***/
// Database settings - change these to your own
define('DB_HOST', 'localhost');
define('DB_NAME', 'pos_development');
define('DB_CHARSET', 'utf8');
define('DB_USER', 'root');
define('DB_PASSWORD', null);

// // Database settings - change these to your own
// define('DB_HOST', 'strixerp.gotdns.com');
// define('DB_NAME', 'pos_development');
// define('DB_PORT', '3306');
// define('DB_CHARSET', 'utf8');
// define('DB_USER', '-------'); // remember // anibal
// define('DB_PASSWORD', '-------'); // ask // man***

// Printer config (and identification)
define('PRINTER_ID', 1); //la impresora en uso (numerada en BD)

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


$conn = $DatabaseBridge->connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME , PRINTER_ID);

// cerrando db (FALTA COLOCARLO EN UN MEJOR LUGAR)
// $conn->close();

// con esta variable verifico si debo imprimir varias veces el error de impresion
$print_error = false;


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


  // PASITO A PASITO (los pasos anteriormente descritos... pero con especificidades)
  // (0) verifico si hay documentos en la tabla current (1)
  //.. en esto particular para la impresora en particular.


  // la conexion de base de datos y la impresora
  // params:
  // .. conexion por si se quiere enviar a otra base de datos
  // .. printer id por si se quiere levantar un hilo despues que vigile a una impresora especifico
  $documentos_imprimiendo = $DatabaseBridge->documentos_imprimiendo($conn, PRINTER_ID);


  // (1) (true) de haber tomo el documento y lo imprimo (...desarrollar ++) (3)
  if ($documentos_imprimiendo->num_rows > 0) { // aqui chequeo si tengo almenos 1

    // solo debe haber una impresion maximo por impresora en la tabla current
    $documento_imprimiendo = $documentos_imprimiendo->fetch_assoc();
    var_dump($documento_imprimiendo);

    $nombre_cajero = $documento_imprimiendo["cashier_name"];

    // segun el tipo de documento (solo facturas de momentos)
    switch ($documento_imprimiendo["document_type_id"]) {
      case "1": // Factura (unico caso de momento)
        $tipo_documento = "Factura";

        // tomo el id de la factura
        $invoice_id = $documento_imprimiendo["document_id"];
        
        // envio al "manejador" respectivo
        $invoiceHandler =  new invoiceHandler();

        // numero del documento
        $info_factura = $invoiceHandler->get_invoice_info($conn, $invoice_id);

        // objeto de los datos de la factura.
        $factura_actual = $info_factura->fetch_assoc();
        $numero_documento = $factura_actual["invoice_number"];


        $respuesta_impresora = $invoiceHandler->printInvoice($conn,$documento_imprimiendo);

        break;
      case "2":// Nota de Credito
        $tipo_documento = "Nota de Credito";

        // tomo el id de la NotadeCredito (nota de credito)
        $creditnote_id = $documento_imprimiendo["document_id"];
        
        // envio al "manejador" respectivo
        $creditnoteHandler =  new creditnoteHandler();

        // // numero del documento
        // $numero_documento = $creditnoteHandler->get_creditnote_info($conn, $creditnote_id);
        // $numero_documento = $numero_documento["credinote_number"];

        $respuesta_impresora = $creditnoteHandler->printCreditnote($conn,$documento_imprimiendo);

        break;
      case "3":// Nota de Debito
        $tipo_documento = "Nota de Debito";

        // tomo el id de la NotadeCredito (nota de credito)
        $debitnote_id = $documento_imprimiendo["document_id"];
        
        // envio al "manejador" respectivo
        $debitnoteHandler =  new debitnoteHandler();

        // // numero del documento
        // $numero_documento = $debitnoteHandler->get_debitnote_info($conn, $debitnote_id);
        // $numero_documento = $numero_documento["debitnote_number"];


        $respuesta_impresora = $debitnoteHandler->printDebitnote($conn,$documento_imprimiendo);

        break;
      case "4":// Nota de entrega (solo items)

        // nota de cualquier otro tipo, 

        break;
      case "5":// documento nulo de caja

       break;

      case "6":// corte de caja
        $respuesta_impresora = $Utils->sendCorte(); 

       break;

       case "7":// cierre de caja
        $respuesta_impresora = $Utils->sendCierre(); 
         
       break;

      default: // Documento indeterminado
        die("Documento indeterminado: (daemon) ". $documento_imprimiendo["document_type_id"] ); 

    }

    // (3) (true) verifico el mensaje del controlador al imprimir, (condiciones de parseo), si todo sale exitoso
    if($respuesta_impresora == "true"){
      // ...si habia un mensaje de error, ahora que se pudo imprimir, deja volver a imprimir mensajes de error
      $print_error = false;

      // ... se toma el individuo en current y se copia a history.
      $DatabaseBridge->mover_a_historico( $conn, $documento_imprimiendo );

      // ... se borra de current
      $DatabaseBridge->borrar_imprimiendo( $conn, $documento_imprimiendo );

      // ... se marca documento impreso
      $DatabaseBridge->marcar_impreso( $conn, $documento_imprimiendo );

      // ... se sobrescribe el mensaje para la impresora de mensajes 
      $mensaje_al_log = "la ".$tipo_documento .": " . $numero_documento .", por cajero ". $nombre_cajero. ", ha impreso con exito.";
      $DatabaseBridge->logWithDoc( $conn, $mensaje_al_log, $documento_imprimiendo, $print_error );

    }else{
      // (3) (false)  verifico el mensaje del controlador al imprimir, (condiciones de parseo), si sale un error
      // ... se mantiene la factura en current (sin cambios)

      echo "la impresora fallo... (hay que colocar los errores en log)\n";
      // ... busco en checkprinter cual puede ser la razon del error.

      // ... se sobrescribe el mensaje para la impresora de mensajes, indicando que hay un error
      $mensaje_al_log = "la ".$tipo_documento .": " . $numero_documento .", por cajero ". $nombre_cajero. ", tiene problemas para imprimir...";
      $DatabaseBridge->logWithDoc( $conn, $mensaje_al_log, $documento_imprimiendo, $print_error );
        // ...si habia un mensaje de error, ahora que se pudo imprimir, deja volver a imprimir mensajes de error
        $print_error = true;
    } 
  
  }else{ 
    // (1) (false) de no haber, busco en pendientes (2)
    // ... reviso que no haya documentos en pendientes
    $documentos_pendientes = $DatabaseBridge->documentos_pendientes($conn, PRINTER_ID);

     // (2) (true) de haber en pendientes (para la impresora especificada), 
     // ...tomo el siguiente en orden FIFO de la cola
    if ($documentos_pendientes->num_rows > 0) {
    
      // datos de la factura pendiente
      $documento_pendiente = $documentos_pendientes->fetch_assoc();

      // ... y lo coloco en current ese invoice.
      $DatabaseBridge->mover_pendiente_a_imprimiendo( $conn, $documento_pendiente );

      // ... se borra de dicha factura de pendiente (END)
      $DatabaseBridge->eliminar_pendiente( $conn, $documento_pendiente );

    }else{
      // (2) (false) de no existir pendientes, no hay que hacer mas nada, solo esperar (Sleep) (END)
      echo("no hay documentos en pendientes, me tomo una siesta. \n");
    }

  }


  // Sleep
  sleep(LOOP_CYCLE);
}
    
    // cd C:\xampp\htdocs\pos_app\src\printer_daemon && php daemon.php
    // D:\xamp\htdocs\Printer-Interpreter
    // cd C:\xampp\htdocs\Printer-Interpreter && php daemon.php
    // php daemon.php
    // cd  d:\xamp\htdocs\Printer-Interpreter && php daemon.php
    // D:\xamp\htdocs\Printer-Interpreter

    // cmd normal:
    // C:\xampp\php\php.exe C:\xampp\htdocs\Printer-Interpreter\daemon.php
    // (de no funcionar, vigilar variables de entorno y lo que vaya diciendo el log.)
    // C:\xampp\xampp_shell.bat cd C:\xampp\htdocs\Printer-Interpreter && php daemon.php
    // C:\xampp\xampp_shell.bat C:\xampp\php\php.exe C:\xampp\htdocs\Printer-Interpreter\daemon.php.

    // background:
    // cd C:\xampp\htdocs\Printer-Interpreter && START /B php daemon.php > daemon.log
?>