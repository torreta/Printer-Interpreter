<?php

  include_once ("TfhkaPHP.php"); 
  include_once ("interpreter.php"); 
  include_once ("Utils.php"); 

  $itObj = new Tfhka(); // printer api

class DatabaseBridge
{
  
  function connect($servername, $username, $password, $dbname, $printer_id){
    /*** (C) CONNECT DATABASE ***/
    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

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
      echo "Se ha registrado una factura a imprimiendo \n";
    } else {
      echo "(al insertar a imrpimiendo) Error: " . $sql . "\n" . mysqli_error($conn);
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
      echo "Se ha borrado la factura pendientes, por haber sido llevada a imprimir. \n";
    } else {
      echo "(al borrar de pendientes) Error: " . $sql . "\n" . mysqli_error($conn);
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
      echo "Se ha registrado la factura en el historial \n";
    } else {
      echo "(al insertar a historial) Error: " . $sql . "\n" . mysqli_error($conn);
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
      echo "Se ha borrado la factura de las imprimiendo, por haber sido completada. \n";
    } else {
      echo "(al borrar de current) Error: " . $sql . "\n" . mysqli_error($conn);
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
      echo "(al actualizar mensaje de la impresora) Error: " . $sql . "\n" . mysqli_error($conn);
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
        echo "(al actualizar mensaje de la impresora)(error impresion)Error: " . $sql . "\n" . mysqli_error($conn);
      }
    }

  }



}
?>