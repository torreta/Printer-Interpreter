<?php

  include_once ("TfhkaPHP.php"); 
  include_once ("interpreter.php"); 
  include_once ("Utils.php"); 
  include_once ("DBConfig.php"); 

  $itObj = new Tfhka(); // printer api

class DatabaseBridge
{
  
  function connect(){
    /*** (C) CONNECT DATABASE ***/
    // Create connection (defined DBConfig.php)
    // these setting are defined on DBConfig.php
    // (if file doesnt exist.... make one... copy the example)
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME , DB_PORT);

    // Check connection
    if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
    }

    // cerrando db (FALTA COLOCARLO EN UN MEJOR LUGAR)
    // $conn->close();

    //retorno la conexion
    return $conn;

  }


  function documentos_imprimiendo($conn, $printer_id="" ){

    // Check connection
    if ($conn->connect_error) {
      die(" (documentos imprimiendo) Connection failed: " . $conn->connect_error);
    }
    
    if($printer_id == ""){
      die("valor vacio el identificador de impresora (documentos imprimiendo) \n");
    }

    $query_documentos_imprimiendo = "SELECT * from dbo_printer_current WHERE printer_id = ".$printer_id.";";
    $documentos_imprimiendo = $conn->query($query_documentos_imprimiendo);

    return  $documentos_imprimiendo;

  }


  function documentos_pendientes($conn, $printer_id="" ){

    // Check connection
    if ($conn->connect_error) {
      die("(documentos pendientes) Connection failed: " . $conn->connect_error);
    }
    
    if($printer_id == ""){
      die("valor vacio el identificador de impresora (documentos pendientes)\n");
    }
    $query_documentos_pendientes = "SELECT * from dbo_printer_pending WHERE printer_id = ".PRINTER_ID.";";
    $documentos_pendientes = $conn->query($query_documentos_pendientes);

    return  $documentos_pendientes;

  }


  function mover_pendiente_a_imprimiendo($conn, $documento_pendiente ){

    // Check connection
    if ($conn->connect_error) {
      die("(mover_pendiente_a_imprimiendo) Connection failed: " . $conn->connect_error);
    }
    
    if($documento_pendiente ==  null){
      die("objeto vacio (mover_pendiente_a_imprimiendo)\n");
    }

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
      echo "\n Se ha movido el documento de pendiente a imprimiendo (current, actual) \n";
    } else {
      echo "(al mover de pendiente a imprimiendo (insertar)) Error: " . $query_a_imprimiendo . "\n" . mysqli_error($conn);
    }

  }


  function eliminar_pendiente($conn, $documento_pendiente ){

    // Check connection
    if ($conn->connect_error) {
      die("(eliminar_pendiente) Connection failed: " . $conn->connect_error);
    }
    
    if($documento_pendiente ==  null){
      die("objeto vacio (eliminar_pendiente)\n");
    }

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
      echo "Se ha borrado el documento de pendientes, por haber sido llevada a imprimir. \n";
    } else {
      echo "(al borrar de pendientes) Error: " . $query_delete_pending . "\n" . mysqli_error($conn);
    }


  }


  function mover_a_historico($conn, $documento_imprimiendo ){

    // Check connection
    if ($conn->connect_error) {
      die("(mover_a_historico) Connection failed: " . $conn->connect_error);
    }
    
    if($documento_imprimiendo ==  null){
      die("objeto vacio (mover_a_historico)\n");
    }

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
      echo "Se ha registrado el documento en el historial de impresos \n";
    } else {
      echo "(al insertar a historial) Error: " . $query_a_historico . "\n" . mysqli_error($conn);
    }

  }


  function borrar_imprimiendo($conn, $documento_imprimiendo ){

    // Check connection
    if ($conn->connect_error) {
      die("(borrar_imprimiendo) Connection failed: " . $conn->connect_error);
    }
    
    if($documento_imprimiendo ==  null){
      die("objeto vacio (borrar_imprimiendo)\n");
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
      echo "Se ha borrado el documeto de la cola de impresion (actual), por haber sido completada. \n";
    } else {
      echo "(al borrar de current) Error: " . $query_delete_current . "\n" . mysqli_error($conn);
    }


  }


  function marcar_impreso($conn, $documento_imprimiendo ){

    // Check connection
    if ($conn->connect_error) {
      die("(marcar_impreso) Connection failed: " . $conn->connect_error);
    }
    
    if($documento_imprimiendo ==  null){
      die("objeto vacio (marcar_impreso)\n");
    }

    // query indenpotente... (no hace nada particular...)
    $query_mark_printed = "SELECT * from `dbo_administration_invoices`;";


    switch ($documento_imprimiendo["document_type_id"]) {
      case "1": // Factura (unico caso de momento)
        $tipo_documento = "factura";

        // tomo el id de la factura
        $invoice_id = $documento_imprimiendo["document_id"];
        
        // ... marcar como impreso
        $query_mark_printed = 
        "UPDATE`dbo_administration_invoices` 
          SET `printed` = 1 
          WHERE `id` = ".$invoice_id.";";
        
          // puedes validar el query aca
          // echo ( $query_mark_printed );

        $marcar_current_registro = $conn->prepare($query_mark_printed);

        if ($marcar_current_registro->execute()) {
          echo "Se ha marcado el documento como impreso.. \n";
        } else {
          echo "(marcar_impreso) Error: " . $query_mark_printed . "\n" . mysqli_error($conn);
        }


        break;
      case "2":// Nota de Credito
        $tipo_documento = "Nota de Credito";

        // tomo el id de la NotadeCredito (nota de credito)
        $creditnote_id = $documento_imprimiendo["document_id"];
        
        // envio al "manejador" respectivo

        break;
      case "3":// Nota de Entrega
        $tipo_documento = "Nota de Credito";
        break;
      case "4":// Nota no Fiscal
        $tipo_documento = "Copia";
        break;
      case "5":// Reimpresion por fechas
        $tipo_documento = "Reimpresion por fechas";
        break;
      case "6":// corte de caja
        $tipo_documento = "Corte";
        break;
      case "7":// cierre de caja
        $tipo_documento = "Cierre";
        break;
      case "8":// cierre de caja
        $tipo_documento = "Cierre doc manual";
        break;
      case "9":// test de impresion
        $tipo_documento = "Test Impresion";
        break;
      case "10":// Resumen por fechas
        $tipo_documento = "Resumen por fechas";
        break;
      default: // Documento indeterminado
        die("Documento indeterminado (bridge) ". $documento_imprimiendo["document_type_id"] ); 
    }


  }


  function sincronizar_numero($conn, $documento_imprimiendo ){
    // la intencion de esta funcion, es dado un id de factura y un numero de factura, verificar si estan alineados,
    // sino, modificar el numero de factura y asi mantener sincronia con el POS Strix

    // ESTE SEGMENTO AUN NO ESTA EN USO.

    // Check connection
    if ($conn->connect_error) {
      die("(marcar_impreso) Connection failed: " . $conn->connect_error);
    }
    
    if($documento_imprimiendo ==  null){
      die("objeto vacio (marcar_impreso)\n");
    }

    // query indenpotente... (no hace nada particular...)
    $query_mark_printed = "SELECT * from `dbo_administration_invoices`;";


    switch ($documento_imprimiendo["document_type_id"]) {
      case "1": // Factura (unico caso de momento)
        $tipo_documento = "factura";

        // tomo el id de la factura
        $invoice_id = $documento_imprimiendo["document_id"];
        
        // ... marcar como impreso
        $query_mark_printed = 
        "UPDATE`dbo_administration_invoices` 
          SET `printed` = 1 
          WHERE `id` = ".$invoice_id.";";
        
          // puedes validar el query aca
          // echo ( $query_mark_printed );

        $marcar_current_registro = $conn->prepare($query_mark_printed);

        if ($marcar_current_registro->execute()) {
          echo "Se ha marcado el documento como impreso.. \n";
        } else {
          echo "(marcar_impreso) Error: " . $query_mark_printed . "\n" . mysqli_error($conn);
        }


        break;
      case "2":// Nota de Credito
        $tipo_documento = "Nota de Credito";

        // tomo el id de la NotadeCredito (nota de credito)
        $creditnote_id = $documento_imprimiendo["document_id"];
        
        // envio al "manejador" respectivo

        break;
      case "3":// Nota de Entrega
        $tipo_documento = "Nota de Credito";
        break;
      case "4":// Nota no Fiscal
        $tipo_documento = "Copia";
        break;
      case "5":// Reimpresion por fechas
        $tipo_documento = "Reimpresion por fechas";
        break;
      case "6":// corte de caja
        $tipo_documento = "Corte";
        break;
      case "7":// cierre de caja
        $tipo_documento = "Cierre";
        break;
      case "8":// cierre de caja
        $tipo_documento = "Cierre doc manual";
        break;
      case "9":// test de impresion
        $tipo_documento = "Test Impresion";
        break;
      case "10":// Resumen por fechas
        $tipo_documento = "Resumen por fechas";
        break;
      default: // Documento indeterminado
        die("Documento indeterminado (bridge) ". $documento_imprimiendo["document_type_id"] ); 
    }


  }


  function logWithDoc($conn, $message, $documento_imprimiendo, $print_error){

    // Check connection
    if ($conn->connect_error) {
      die("(logWithDoc) Connection failed: " . $conn->connect_error);
    }
    
    if($documento_imprimiendo ==  null){
      die("objeto vacio (logWithDoc)\n");
    }

    if($message ==  ""){
      die("mensaje vacio (logWithDoc)\n");
    }

    $query_update_message = 
      "INSERT dbo_printer_messages(
        id, 
        message, 
        printer_id, 
        user_id, 
        cashier_name) 
      VALUES ("
        .$documento_imprimiendo["printer_id"].",'"
        .$message."', "
        .$documento_imprimiendo["printer_id"].", "
        .$documento_imprimiendo["user_id"].", '"
        .$documento_imprimiendo["cashier_name"]."')
      ON DUPLICATE KEY UPDATE 
        message = '".$message."'
      ;";

    // puedes validar el query aca
    // echo ( $query_update_message );

    $actualizar_mensaje_impresora = $conn->prepare($query_update_message);

    if ($actualizar_mensaje_impresora->execute()) {
      echo "se ha actualizado el mensaje de la impresora. \n";
    } else {
      echo "(al actualizar mensaje de la impresora) Error: " . $query_update_message . "\n" . mysqli_error($conn);
    }

    // esta comprovacion evita que se repitan los errores en el log historico una vez falle una vez
    if(!$print_error){

        $query_log_message = 
        "INSERT INTO dbo_printer_log(
          message, 
          printer_id, 
          user_id, 
          cashier_name) 
        VALUES ('"
        .$message."', "
        .$documento_imprimiendo["printer_id"].", "
        .$documento_imprimiendo["user_id"].", '"
        .$documento_imprimiendo["cashier_name"]."');";

        // puedes validar el query aca
        // echo ( $query_log_message );

        $actualizar_mensaje_impresora = $conn->prepare($query_log_message);

        if ($actualizar_mensaje_impresora->execute()) {
        echo "se ha escrito un registro nuevo en log de impresiones.  \n";
        
      } else {
        echo "(al actualizar mensaje de la impresora)(error impresion)Error: " . $query_log_message . "\n" . mysqli_error($conn);
      }
    }

  }

  function insertDataOnLedger($conn, $documentId, $documentType){
    
    if ($conn->connect_error) {
      die("(insertDataOnLedger) Connection failed: " . $conn->connect_error);
    }
    if (!$documentId) {
      die("dato vital vacio (insertDataOnLedger)\n");
    }
    if (!$documentType) {
        die("dato vital vacio (insertDataOnLedger)\n");
    }

    $checkDocumentPendingQueries = "SELECT document_id, document_type FROM dbo_printer_ledger_pending";
    $checkDocumentPending = mysqli_query($conn, $checkDocumentPendingQueries);

    $arrayRequest = array();

    if ($checkDocumentPending) {
        while($row = mysqli_fetch_assoc($checkDocumentPending)){
            $arrayRequest[] = $row;
        }
    } else {
        echo "No hay datos en la tabla temporal";
    }

    $arrayDocumentCurrent = array("document_id" => $documentId, "document_type" => $documentType);
    array_push($arrayRequest, $arrayDocumentCurrent);

    $countRequest = count($arrayRequest);

    for ($i = 0; $i < $countRequest; $i++) {
        $arrayParams = $arrayRequest[$i];
        $request = json_encode($arrayParams);
        $construct_url = array(
          "startUrl" => "http://",
          "host" => DB_HOST,
          "endUrl" => ":3000/api/ledger/sales/printer_update"
        );
        $url = $construct_url["startUrl"] . $construct_url["host"] . $construct_url["endUrl"];
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
    
        curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    
        curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        echo "status petición http: ".$status."\n";
    
        
        if($status == 0){
            $recordExistsSQL = "SELECT COUNT(*) AS count_records FROM dbo_printer_ledger_pending WHERE document_id = '{$arrayParams["document_id"]}' AND document_type = '{$arrayParams["document_type"]}'";
            
            $recordExistsQuery = $conn->query($recordExistsSQL);
            $recordExists = $recordExistsQuery->fetch_assoc();
            
            if ($recordExists["count_records"] > 0) {
                echo "No se registró en el libro de Ventas. Ya se encontraba registrada en la tabla pending\n";
                continue;
            }
            $sql = "INSERT INTO dbo_printer_ledger_pending (document_id, document_type) VALUES ({$arrayParams["document_id"]}, {$arrayParams["document_type"]})";    
            $stmt = $conn->prepare($sql);
            
            if ($stmt->execute()) {
                echo "Datos insertados en la tabla temporal\n";
            } else {
                echo "Error al insertar datos: " . $stmt->error."\n";
            }
            
            $stmt->close();
            
        } else if ($status == 200){
            echo "Se ha insertado la factura en el libro de ventas\n";

            $checkDocumentPendingSQL = "SELECT * FROM dbo_printer_ledger_pending WHERE document_id = '{$arrayParams["document_id"]}' AND document_type = '{$arrayParams["document_type"]}'";
            $checkDocumentPendingQueries = $conn->query($checkDocumentPendingSQL);

            if($checkDocumentPendingQueries->num_rows > 0){
                $documentPending = $checkDocumentPendingQueries->fetch_assoc();
                $deleteDocumentRecordedSQL = "DELETE FROM dbo_printer_ledger_pending WHERE id = {$documentPending['id']}";

                $deleteDocumentRecorded = $conn->prepare($deleteDocumentRecordedSQL);

                if ($deleteDocumentRecorded->execute()) {
                    echo "Se ha borrado el documento de pendientes, al haberse creado el registro en el libro de ventas\n";
                } else {
                    echo "(al borrar de pendientes) Error: " . $deleteDocumentRecorded . "\n" . mysqli_error($conn);
                }
            }

            curl_close($curl);
        } else if ($status == 400){
            echo "Bad Request al insertar en el libro de ventas";
        }
    }
    
  }



}
?>