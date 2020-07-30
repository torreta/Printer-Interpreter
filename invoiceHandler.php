<?php

  include_once ("TfhkaPHP.php"); 
  include_once ("interpreter.php"); 
  include_once ("Utils.php"); 
  include_once ("DatabaseBridge.php"); 

  $itObj = new Tfhka(); // printer api

class invoiceHandler
{
  
  function get_invoice_info($conn, $invoice_id ){

    // Check connection
    if ($conn->connect_error) {
      die("(get_invoice_info) Connection failed: " . $conn->connect_error);
    }
    
    if($invoice_id ==  null || $invoice_id ==  "" ){
      die("dato vital vacio (get_invoice_info)\n");
    }

    $query_info_factura = "SELECT * FROM dbo_administration_invoices WHERE id = ".$invoice_id.";";
    $info_factura = null;
    $info_factura = $conn->query($query_info_factura);

    if ($info_factura->num_rows == 0) { die("factura con ese id no existe"); }

    return  $info_factura;

  }

  
  function get_info_fiscal($conn, $invoice_id ){

    // Check connection
    if ($conn->connect_error) {
      die("(get_invoice_info) Connection failed: " . $conn->connect_error);
    }
    
    if($invoice_id ==  null || $invoice_id ==  "" ){
      die("dato vital vacio (get_invoice_info)\n");
    }

    // detalles fiscales del documento
    $query_info_fiscal_factura = 
      "SELECT
        dbo_administration_invoices.invoice_number,
        dbo_administration_invoices.tax_id,
        DATE_FORMAT( dbo_administration_invoices.createdAt, '%d-%m-%Y') as createdAt,
        dbo_sales_clients.name,
        dbo_sales_clients.last_name,
        dbo_sales_clients.telephone,
        dbo_sales_clients.identification_number,
        dbo_sales_clients.identification_type_id,
        dbo_sales_clients.direction,
        dbo_config_identifications_types.`name`  as identification_type_name,
        concat(dbo_config_identifications_types.`name`,dbo_sales_clients.identification_number) as complete_identification,
        dbo_system_users.name as user_name,
        dbo_system_users.last_name as user_lastname,
        dbo_system_users.rol_id 
      FROM 
        dbo_administration_invoices
      join dbo_sales_clients on dbo_administration_invoices.client_id = dbo_sales_clients.id
      join dbo_config_identifications_types on dbo_sales_clients.identification_type_id = dbo_config_identifications_types.id
      join dbo_system_users on dbo_administration_invoices.user_id = dbo_system_users.id
      WHERE dbo_administration_invoices.id = ".$invoice_id.";";

    $info_fiscal_factura = $conn->query($query_info_fiscal_factura);

    if ($info_fiscal_factura->num_rows == 0) { die("factura con info fiscal con ese id no existe"); }

    return  $info_fiscal_factura;

  }

  function get_invoice_items($conn, $invoice_id ){

    // Check connection
    if ($conn->connect_error) {
      die("(get_invoice_items) Connection failed: " . $conn->connect_error);
    }
    
    if($invoice_id ==  null || $invoice_id ==  "" ){
      die("dato vital vacio (get_invoice_items)\n");
    }
    
    // inicializo una instancia de interprete para el tipo de doc.
    // ...(hago una instancia del interprete del tipo de doc)
    $interpreter = new interpreter();

    $factura_en_contruccion = array();
    $index_counter = 0;
    $index_inverse_counter = 0;


    $query_items_factura = 
      "SELECT
        dbo_administration_invoices_items.id,
        dbo_administration_invoices_items.invoice_id,
        dbo_administration_invoices_items.price,
        dbo_administration_invoices_items.quantity, 
        dbo_administration_invoices_items.tax_id,
        dbo_administration_invoices_items.tax_base,
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

    $items_factura = $conn->query($query_items_factura);

    if (!($items_factura->num_rows > 0)) {
      die("no hay items asociados a esa factura");
    }

    // output data of each row
    while($item = $items_factura->fetch_assoc()) {
      // echo "\n";
      // echo "price: " . $item["price"]. " - quantity: " . $item["quantity"]. ", description " . $item["description"];
      // echo "\n";

      // proximamente al interpreter
      // .. el tax rate, deberia pasarse en texto (ya, pero se llama observation en el query, esta en string)
      $factura_en_contruccion[$index_counter] = $interpreter->translateLine($item["observation"],$item["tax_base"],$item["quantity"],$item["description"])."\n";
      // $factura_en_contruccion[$index_counter] = $interpreter->translateLine($item["observation"],round($item["price"]/1.12, 2),$item["quantity"],$item["description"])."\n";
      $index_counter++;
    }

    //cierre de factura (viene despues de los items)
    $factura_en_contruccion[$index_counter] = "101";

    return  $factura_en_contruccion;

  }



  function printInvoice($conn, $documento_imprimiendo ){

    // Check connection
    if ($conn->connect_error) {
      die("(get_invoice_info) Connection failed: " . $conn->connect_error);
    }
    

    if($documento_imprimiendo ==  null){
      die("dato vital vacio (get_invoice_info)\n");
    }

    // tomo el id de la factura
    $invoice_id = $documento_imprimiendo["document_id"];

    // detalles de documento
    $info_factura = $this->get_invoice_info($conn,$invoice_id);

    // objeto de los datos de la factura.
    $factura_actual = $info_factura->fetch_assoc();

    // informacion fiscal
    $info_fiscal_factura =  $this->get_info_fiscal($conn,$invoice_id);

    // objeto informacion fiscal factura
    $factura_fiscal_actual = $info_fiscal_factura->fetch_assoc();


    // numero de factura
    $numero_factura = $factura_actual["invoice_number"];

    $nombre_cajero = $documento_imprimiendo["cashier_name"];

    echo "\n";
    echo "el documento a imprimir es la factura " . $numero_factura .", por cajero ". $nombre_cajero. "\n ";
    echo "\n";

    // inicializo una instancia de interprete para el tipo de doc.
    // ...(hago una instancia del interprete del tipo de doc)
    $interpreter = new interpreter();

    // counter for translation
    $factura_en_contruccion = array();
    $index_counter = 0;
    $index_inverse_counter = 0;

    // consultar informacion fiscal de la factura antes de armarla
    $infoFiscalTraducida = $interpreter->translateFiscalInfoArray($factura_fiscal_actual);

    // arreglo de los items de la factura
    $items_factura = $this->get_invoice_items($conn,$invoice_id);

    // concateno la informacion fiscal a la de los items de la factura
    $factura_en_contruccion = $infoFiscalTraducida + $items_factura;

    //cierre de factura (lo coloque en los items de una vez)
    //.. si quiero luego colocar pie de factura aqui lo puedo hacer con el size de $items_factura + 1 como indice y sumo
    // $factura_en_contruccion[$index_counter] = "101";

    echo "\n";
    var_dump($factura_en_contruccion) ;
    echo "\n";
    
    // creo el archivo de la factura y lo mando a imprimir
    $Utils = new Utils();
    $filename = "Factura".$numero_factura.".txt";	
    $file = $Utils->printFileFromArray($factura_en_contruccion, $filename);
    
    $respuesta_impresora = $Utils->printFile($filename);
    
    if($respuesta_impresora == "true"){

      return "true";

    }else{
      echo "la impresora fallo... (hay que colocar los errores en log)\n";
      return "false";

    }


  }




}
?>

<!-- 
// detalles de documento
        $info_factura = $invoiceHandler->get_invoice_info($conn,$invoice_id);


        if ($info_factura->num_rows == 0) { die("factura con ese id no existe"); }

        // detalles fiscales del documento
        $query_info_fiscal_factura = 
          "SELECT
            dbo_administration_invoices.invoice_number,
            dbo_administration_invoices.tax_id,
            DATE_FORMAT( dbo_administration_invoices.createdAt, '%d-%m-%Y') as createdAt,
            dbo_sales_clients.name,
            dbo_sales_clients.last_name,
            dbo_sales_clients.telephone,
            dbo_sales_clients.identification_number,
            dbo_sales_clients.identification_type_id,
            dbo_sales_clients.direction,
            dbo_config_identifications_types.`name`  as identification_type_name,
            concat(dbo_config_identifications_types.`name`,dbo_sales_clients.identification_number) as complete_identification,
            dbo_system_users.name as user_name,
            dbo_system_users.last_name as user_lastname,
            dbo_system_users.rol_id 
          FROM 
            dbo_administration_invoices
          join dbo_sales_clients on dbo_administration_invoices.client_id = dbo_sales_clients.id
          join dbo_config_identifications_types on dbo_sales_clients.identification_type_id = dbo_config_identifications_types.id
          join dbo_system_users on dbo_administration_invoices.user_id = dbo_system_users.id
          WHERE dbo_administration_invoices.id = ".$invoice_id.";";

        $info_fiscal_factura = null;
        $info_fiscal_factura = $conn->query($query_info_fiscal_factura);

        if ($info_fiscal_factura->num_rows == 0) { die("factura con info fiscal con ese id no existe"); }

        // info fiscal
        $factura_fiscal_actual = $info_fiscal_factura->fetch_assoc();


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
            dbo_administration_invoices_items.tax_base,
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
            $index_inverse_counter = 0;

            // consultar informacion fiscal de la factura antes de armarla
            $infoFiscalTraducida = $interpreter->translateFiscalInfoArray($factura_fiscal_actual);

            // output data of each row
            while($item = $items_factura->fetch_assoc()) {
              // echo "\n";
              // echo "price: " . $item["price"]. " - quantity: " . $item["quantity"]. ", description " . $item["description"];
              // echo "\n";

              // proximamente al interpreter
              // .. el tax rate, deberia pasarse en texto (ya, pero se llama observation en el query, esta en string)
              $factura_en_contruccion[$index_counter] = $interpreter->translateLine($item["observation"],$item["tax_base"],$item["quantity"],$item["description"])."\n";
              // $factura_en_contruccion[$index_counter] = $interpreter->translateLine($item["observation"],round($item["price"]/1.12, 2),$item["quantity"],$item["description"])."\n";
              $index_counter++;
            }

            // concateno la informacion fiscal a la de los items de la factura
            $factura_en_contruccion = $infoFiscalTraducida + $factura_en_contruccion;

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

            // verifico si la impresora esta encendida
            // ... colocar condicionales para proceder (FALTA)
            $impresora_encendida =  $itObj->CheckFprinter();
            $impresora_encendida = $interpreter->check_impresora_encendida($impresora_encendida);
                          
            // puedes validar el query aca
            echo ( "impresora encendida \n");
            echo ( $impresora_encendida );
            echo ( "\n");

            // verifico estados de la impresora
            $respuesta_impresora_estado =  $itObj->ReadFpStatus();
            $respuesta_impresora_estado = $interpreter->check_estado_impresora($respuesta_impresora_estado);

                          
            // puedes validar el query aca
            echo ( "impresora estado de impresora aparte \n");
            echo ( $respuesta_impresora_estado );
            echo ( "\n");

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
              // ...si habia un mensaje de error, ahora que se pudo imprimir, deja volver a imprimir mensajes de error
              $print_error = false;

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
              $mensaje_al_log = "la factura " . $numero_factura .", por cajero ". $nombre_cajero. ", ha impreso con exito.";

              $query_update_message = 
              "INSERT dbo_printer_messages(
                  id, 
                  message, 
                  printer_id, 
                  user_id, 
                  cashier_name) 
                VALUES ("
                  .$documento_imprimiendo["printer_id"].",'"
                  .$mensaje_al_log."', "
                  .$documento_imprimiendo["printer_id"].", "
                  .$documento_imprimiendo["user_id"].", '"
                  .$documento_imprimiendo["cashier_name"]."')
                ON DUPLICATE KEY UPDATE 
                  message = '".$mensaje_al_log."'
                ;";

              // puedes validar el query aca
              // echo ( $query_update_message );

              $actualizar_mensaje_impresora = $conn->prepare($query_update_message);

              if ($actualizar_mensaje_impresora->execute()) {
                echo "se ha actualizado el mensaje de la impresora. \n";
              } else {
                echo "(al actualizar mensaje de la impresora) Error: " . $sql . "\n" . mysqli_error($conn);
              }
              
              // ... se coloca el mensaje en la tabla log de mensajes (END)
              $query_log_message = 
              "INSERT INTO dbo_printer_log(
                  message, 
                  printer_id, 
                  user_id, 
                  cashier_name) 
              VALUES ('"
              .$mensaje_al_log."', "
              .$documento_imprimiendo["printer_id"].", "
              .$documento_imprimiendo["user_id"].", '"
              .$documento_imprimiendo["cashier_name"]."');";

              // puedes validar el query aca
              // echo ( $query_log_message );

              $actualizar_mensaje_impresora = $conn->prepare($query_log_message);

              if ($actualizar_mensaje_impresora->execute()) {
              echo "se ha escrito un registro nuevo en log de impresiones.  \n";
              } else {
              echo "(al actualizar mensaje de la impresora) Error: " . $sql . "\n" . mysqli_error($conn);
              }


            }else{
              // (3) (false)  verifico el mensaje del controlador al imprimir, (condiciones de parseo), si sale un error
              
              // ... se mantiene la factura en current (sin cambios)

              echo "la impresora fallo... (hay que colocar los errores en log)\n";
              // ... busco en checkprinter cual puede ser la razon del error.



              // ... se sobrescribe el mensaje para la impresora de mensajes, indicando que hay un error
              $mensaje_al_log = "la factura " . $numero_factura .", por cajero ". $nombre_cajero. ", tiene problemas para imprimir...";
                
              $query_update_message = 
              "INSERT dbo_printer_messages(
                  id, 
                  message, 
                  printer_id, 
                  user_id, 
                  cashier_name) 
                VALUES ("
                  .$documento_imprimiendo["printer_id"].",'"
                  .$mensaje_al_log."', "
                  .$documento_imprimiendo["printer_id"].", "
                  .$documento_imprimiendo["user_id"].", '"
                  .$documento_imprimiendo["cashier_name"]."')
                ON DUPLICATE KEY UPDATE 
                  message = '".$mensaje_al_log."'
                ;";

              // puedes validar el query aca
              // echo ( $query_update_message );

              $actualizar_mensaje_impresora = $conn->prepare($query_update_message);

              if ($actualizar_mensaje_impresora->execute()) {
                echo "se ha actualizado el mensaje de la impresora, con el mensaje de error. \n";
              } else {
                echo "(al actualizar mensaje de la impresora)(error impresion) Error: " . $sql . "\n" . mysqli_error($conn);
              }
              
              // ... se coloca el mensaje en la tabla log de mensajes (END)
              // ... pero chequeo si aun no ha sido ya colocado en el log, para que no repita el mismo error varias veces

              // con esta variable verifico si debo imprimir varias veces el error de impresion
              if(!$print_error){

                $query_log_message = 
                "INSERT INTO dbo_printer_log(
                  message, 
                  printer_id, 
                  user_id, 
                  cashier_name) 
                VALUES ('"
                .$mensaje_al_log."', "
                .$documento_imprimiendo["printer_id"].", "
                .$documento_imprimiendo["user_id"].", '"
                .$documento_imprimiendo["cashier_name"]."');";
  
                // puedes validar el query aca
                // echo ( $query_log_message );
  
                $actualizar_mensaje_impresora = $conn->prepare($query_log_message);
  
                if ($actualizar_mensaje_impresora->execute()) {
                echo "se ha escrito un registro nuevo en log de impresiones.  \n";
                
                // esta variable la modifico para indicar que el error ya fue impreso 
                // ... y asi no repetirlo.
                $print_error = true; 

                } else {
                  echo "(al actualizar mensaje de la impresora)(error impresion)Error: " . $sql . "\n" . mysqli_error($conn);
                }
              }
              
            }

          } else {
            echo "no hay items asociados a esa factura \n";
          } -->