<?php

  include_once ("TfhkaPHP.php"); 
  include_once ("interpreter.php"); 
  include_once ("Utils.php"); 
  include_once ("DatabaseBridge.php"); 

  $itObj = new Tfhka(); // printer api

class creditnoteHandler
{

  function get_creditnote_info($conn, $creditnote_id)
  {

    // Check connection
    if ($conn->connect_error) {
      die("(get_creditnote_info) Connection failed: " . $conn->connect_error);
    }

    if ($creditnote_id ==  null || $creditnote_id ==  "") {
      die("dato vital vacio (get_creditnote_info)\n");
    }

    $query_info_creditnote = "SELECT * FROM dbo_finance_creditnotes WHERE id = " . $creditnote_id . ";";
    $info_creditnote = null;
    $info_creditnote = $conn->query($query_info_creditnote);

    if ($info_creditnote->num_rows == 0) {
      die("Nota de credito con ese id no existe");
    }

    return  $info_creditnote;
  }

  
  function get_info_fiscal($conn, $creditnote_id)
  {

    // Check connection
    if ($conn->connect_error) {
      die("(get_creditnote_info) Connection failed: " . $conn->connect_error);
    }

    if ($creditnote_id ==  null || $creditnote_id ==  "") {
      die("dato vital vacio (get_creditnote_info)\n");
    }

    // detalles fiscales del documento
    $query_info_fiscal_creditnote =
      "SELECT
        IFNULL(dbo_finance_creditnotes.creditnote_number,'na') as creditnote_number,
        IFNULL(dbo_administration_invoices.invoice_number,'na') as invoice_number,
        IFNULL(dbo_administration_invoices.fiscal,'0') as fiscal,
        dbo_finance_creditnotes.observations,
        dbo_finance_creditnotes.creditnote_amount,
        DATE_FORMAT( dbo_finance_creditnotes.createdAt, '%d-%m-%Y') as createdAt,
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
        dbo_system_users.rol_id,
        'Z00000001' as printer_serial
      FROM
        dbo_finance_creditnotes
      left join dbo_sales_clients on dbo_finance_creditnotes.client_id = dbo_sales_clients.id
      left join dbo_config_identifications_types on dbo_sales_clients.identification_type_id = dbo_config_identifications_types.id
      left join dbo_system_users on dbo_finance_creditnotes.user_id = dbo_system_users.id
      left join dbo_administration_invoices on dbo_finance_creditnotes.invoice_id = dbo_administration_invoices.id
      where dbo_finance_creditnotes.id =" . $creditnote_id . ";";

    var_dump("query a revisarsisimo");
    var_dump($query_info_fiscal_creditnote);

    $info_fiscal_creditnote = $conn->query($query_info_fiscal_creditnote);

    if ($info_fiscal_creditnote->num_rows == 0) {
      die("nota de credito con info fiscal con ese id no existe, o faltan datos");
    }

    return  $info_fiscal_creditnote;
  }

  function get_creditnote_items($conn, $creditnote_id, $tipo_de_nota, $total)
  {

    // Check connection
    if ($conn->connect_error) {
      die("(get_creditnote_items) Connection failed: " . $conn->connect_error);
    }

    if ($creditnote_id ==  null || $creditnote_id ==  "") {
      die("dato vital vacio (get_creditnote_items)\n");
    }

    // inicializo una instancia de interprete para el tipo de doc.
    // ...(hago una instancia del interprete del tipo de doc)
    $interpreter = new interpreter();
    $interpreter_nofiscal = new interpreter_nofiscal();

    $creditnote_en_contruccion = array();
    $index_counter = 0;
    $index_inverse_counter = 0;


    $query_items_creditnote =
      "SELECT
        dbo_finance_creditnotes_items.creditnote_id,
        dbo_storage_products.`code`,
        dbo_storage_products.description,
        dbo_administration_invoices_items.price as check_price,
        dbo_administration_invoices_items_prices.unit_price_after_discount as price,
        dbo_finance_creditnotes_items.net_amount,
        dbo_finance_creditnotes_items.product_quantity,
        dbo_finance_creditnotes_items.observations,
        dbo_config_taxes.tax_code,
        dbo_config_taxes.observation as tax_observation
      FROM `dbo_finance_creditnotes_items`
      join dbo_finance_creditnotes on dbo_finance_creditnotes.id = dbo_finance_creditnotes_items.creditnote_id
      join dbo_administration_invoices on dbo_administration_invoices.id = dbo_finance_creditnotes.invoice_id
      join dbo_storage_products on dbo_finance_creditnotes_items.product_id = dbo_storage_products.id
      join dbo_administration_invoices_items on dbo_administration_invoices_items.product_id = dbo_finance_creditnotes_items.product_id and dbo_administration_invoices_items.invoice_id = dbo_finance_creditnotes.invoice_id
      join dbo_config_taxes on dbo_config_taxes.id = dbo_administration_invoices_items.tax_id
      join dbo_administration_invoices_items_prices on dbo_administration_invoices_items.id = dbo_administration_invoices_items_prices.invoice_item_id 
      and dbo_administration_invoices_items_prices.currency_id = 2
      WHERE 	dbo_finance_creditnotes_items.creditnote_id = " . $creditnote_id . ";
    ";

    $items_nota_credito = $conn->query($query_items_creditnote);
    var_dump($query_items_creditnote);

    if (!($items_nota_credito->num_rows > 0)) {
      var_dump("no hay items asociados a esa nota de credito");
      return "false";
    } else {

      // output data of each row
      while ($item = $items_nota_credito->fetch_assoc()) {
        echo "\n";
        echo "price: " . $item["price"] . " - quantity: " . $item["product_quantity"] . ", description " . $item["description"] . ", observation: " . $item["observations"];
        echo "\n";

        // proximamente al interpreter
        // .. el tax rate, deberia pasarse en texto (ya, pero se llama observation en el query, esta en string)
        // $tasa="", $precio = "", $cant = "", $desc = ""
        // $creditnote_en_contruccion[$index_counter] = $interpreter->translateLineCommentCredito($item["observations"])."\n";
        // $index_counter++;
        // $creditnote_en_contruccion[$index_counter] = $interpreter->translateLineCredito($item["tax_observation"],$item["price"],$item["product_quantity"],$item["description"])."\n";
        // $index_counter++;


        if ($tipo_de_nota == "fiscal") {
          // proximamente al interpreter
          // .. el tax rate, deberia pasarse en texto (ya, pero se llama observation en el query, esta en string)
          // $tasa="", $precio = "", $cant = "", $desc = ""
          $creditnote_en_contruccion[$index_counter] = $interpreter->translateLineCredito($item["tax_code"], $item["price"], $item["product_quantity"], $item["description"]) . "\n";
          $index_counter++;
        } else {
          // el interpreter en los no fiscales genera 2 lineas separadas, si es un item de de  mas de 2 items

          // // .. el tax rate, deberia pasarse en texto (ya, pero se llama observation en el query, esta en string)
          // $tasa="", $precio = "", $cant = "", $desc = ""
          if ($item["product_quantity"] == "1") {
            $creditnote_en_contruccion[$index_counter] = $interpreter_nofiscal->translateLineCredito($item["tax_observation"], $item["net_amount"], $item["product_quantity"], $item["description"]) . "\n";
            $index_counter++;
          } else {
            // $precio = "", $cant = ""
            $creditnote_en_contruccion[$index_counter] = $interpreter_nofiscal->translateLinePrice($item["tax_observation"], $item["net_amount"], $item["product_quantity"], $item["description"]) . "\n";
            $index_counter++;
            // $tasa="", $desc = ""
            $creditnote_en_contruccion[$index_counter] = $interpreter_nofiscal->translateLineDesc($item["tax_observation"], $item["net_amount"], $item["product_quantity"], $item["description"]) . "\n";
            $index_counter++;
          }
        }
      }
    }


    if ($tipo_de_nota == "fiscal") {
      //cierre de factura (viene despues de los items)
      $creditnote_en_contruccion[$index_counter] =  "101" . "\n";

      if (D_IGTF == true) {
        $index_counter++; // sino sobre escribo el pago con 199
        $creditnote_en_contruccion[$index_counter] = "199";
      }
    } else {
      $creditnote_en_contruccion[$index_counter] =  $interpreter_nofiscal->separador();
      $index_counter++;

      // total
      $creditnote_en_contruccion[$index_counter] = $interpreter_nofiscal->translateFinalTotal($total) . "\n";
      $index_counter++;

      $creditnote_en_contruccion[$index_counter] = "810";
    }

    return  $creditnote_en_contruccion;
  }


  function printCreditnote($conn, $documento_imprimiendo ){

    // Check connection
    if ($conn->connect_error) {
      die("(creditnoteHandler) Connection failed: " . $conn->connect_error);
    }
    

    if ($documento_imprimiendo ==  null) {
      die("dato vital vacio (creditnoteHandler)\n");
    }

    // tomo el id de la nota de credito
    $creditnote_id = $documento_imprimiendo["document_id"];

    // detalles de documento
    $info_creditnote = $this->get_creditnote_info($conn, $creditnote_id);

    // objeto de los datos de la nota de credito.
    $nota_credito_actual = $info_creditnote->fetch_assoc();

    // informacion fiscal
    $info_fiscal_nota_credito =  $this->get_info_fiscal($conn,$creditnote_id);

    // objeto informacion fiscal nota de credito
    $nota_credito_actual = $info_fiscal_nota_credito->fetch_assoc();

    // info de nota de credito
    $numero_creditnote = $nota_credito_actual["creditnote_number"];
    $amount = $nota_credito_actual["creditnote_amount"];

    // probando
    $total = $amount;

    // nombre Cajero
    $nombre_cajero = $documento_imprimiendo["cashier_name"];

    // tipo
    $es_fiscal = $nota_credito_actual["fiscal"];
    $tipo_de_nota = ($es_fiscal == "1") ? "fiscal" : "no fiscal";


    echo "\n";
    echo "el documento a imprimir es la nota de credito de numero: " . $numero_creditnote . ", por cajero " . $nombre_cajero . "\n ";
    echo "\n";

    // inicializo una instancia de interprete para el tipo de doc.
    // ...(hago una instancia del interprete del tipo de doc)
    $interpreter = new interpreter();
    $interpreter_nofiscal = new interpreter_nofiscal();

    // counter for translation
    $creditnote_en_contruccion = array();
    $index_counter = 0;
    $index_inverse_counter = 0;

    // consultar informacion fiscal de la factura antes de armarla
    $infoFiscalTraducida = $interpreter->translateFiscalInfoArrayCreditnote($nota_credito_actual);

    if ($tipo_de_nota == "fiscal") {
      // consultar informacion fiscal de la factura antes de armarla
      $infoFiscalTraducida = $interpreter->translateFiscalInfoArrayCreditnote($nota_credito_actual);

      // arreglo de los items de la factura
      // $items_nota = $this->get_invoice_items($conn ,$invoice_id, $tipo_de_nota, $subtotal, $tax, $total);

      // arreglo de los items de la factura
      $items_nota = $this->get_creditnote_items($conn, $creditnote_id, $tipo_de_nota, $total);


      // en caso de que la nota de credito no tenia items, esto aplica
      if ($items_nota == "false") {
        $items_nota_extra = array();
        $items_nota_extra[1] = $interpreter->translateLineCredito("Sin IVA", $amount, 1, "Saldo A Favor") . "\n";

        $cierre = array();
        $cierre[2] = "101" . "\n";

        //cierre de factura (viene despues de los items)
        // $factura_en_contruccion[$index_counter] = "105";
        if (D_IGTF == true) {
         $cierre[3] = "199";
        }

        $creditnote_en_contruccion = $infoFiscalTraducida +  $items_nota_extra + $cierre;
      } else {

        // concateno la informacion fiscal a la de los items de la factura
        $creditnote_en_contruccion = $infoFiscalTraducida + $items_nota;

      }
    } else {
      // consultar informacion fiscal de la factura antes de armarla
      $infoFiscalTraducida = $interpreter_nofiscal->translateFiscalInfoArrayCreditnote($nota_credito_actual);

      // arreglo de los items de la factura
      // $items_nota = $this->get_invoice_items($conn ,$invoice_id, $tipo_de_nota, $subtotal, $tax, $total);

      // arreglo de los items de la factura
      $items_nota = $this->get_creditnote_items($conn, $creditnote_id, $tipo_de_nota, $total);


      // en caso de que la nota de credito no tenia items, esto aplica
      if ($items_nota == "false") {
        $items_nota_extra = array();
        $items_nota_extra[1] =  $interpreter_nofiscal->translateLineCredito("Sin IVA", $amount, 1, "Saldo A Favor") . "\n";

        $cierre = array();
        $cierre[2] = "810";
        $creditnote_en_contruccion = $infoFiscalTraducida +  $items_nota_extra + $cierre;
      } else {
        // concateno la informacion fiscal a la de los items de la factura
        $creditnote_en_contruccion = $infoFiscalTraducida + $items_nota;
      }
    }

    //cierre de factura (lo coloque en los items de una vez)
    //.. si quiero luego colocar pie de factura aqui lo puedo hacer con el size de $items_factura + 1 como indice y sumo
    // $creditnote_en_contruccion[$index_counter] = "101";

    echo "\n";
    var_dump($creditnote_en_contruccion);
    echo "\n";

    // creo el archivo de la factura y lo mando a imprimir
    $Utils = new Utils();
    $filename = "NC/NotadeCredito" . $numero_creditnote . ".txt";
    $file = $Utils->printFileFromArray($creditnote_en_contruccion, $filename);

    // $this->cierre_documento();

    $respuesta_impresora = $Utils->printFile($filename);
    // linea para emular impresion exitosa.
    // $respuesta_impresora = "true";

    $respuesta_status = $Utils->system_status();

    // aqui es donde deberia llamar las respectivas funciones que
    // sincronizan los numeros de factura, corte, etc
    if ($tipo_de_nota == "fiscal" && $respuesta_status != "") {
      echo "Ultima nota de crédito impresa a sincronizar: " . $respuesta_status[7];
      $creditnote_number = $respuesta_status[7];
      $this->syncronize_creditnote($conn, $creditnote_id, $creditnote_number);
      $this->syncronize_status($conn, $respuesta_status);

    }

    if ($respuesta_impresora == "true") {
      return "true";
    } else {
      echo "la impresora fallo... (hay que colocar los errores en log)\n";
      return "false";
    }
  }

  function syncronize_creditnote($conn, $creditnote_id, $creditnote_number)
  {

    // Check connection
    if ($conn->connect_error) {
      die("(syncronize_creditnote) Connection failed: " . $conn->connect_error);
    }

    if ($creditnote_id ==  null || $creditnote_id ==  "") {
      die("dato vital vacio (syncronize_creditnote) (creditnote_id)\n");
    }

    if ($creditnote_number ==  null || $creditnote_number ==  "") {
      die("dato vital vacio (syncronize_creditnote) (creditnote_number)\n");
    }

    // cambiar los datos fiscales de las facturas.
    $query_sincronizar_nro_creditnote = "UPDATE dbo_finance_creditnotes SET creditnote_number = " . $creditnote_number . " WHERE id = " . $creditnote_id;

    $cambiar_registro = $conn->prepare($query_sincronizar_nro_creditnote);

    if ($cambiar_registro->execute()) {
      echo "Se ha cambiado el nro nota de crédito un del a imprimiendo (sync)\n";
    } else {
      echo "(al cambiar el registro de numero de nota de crédito) Error: " . $query_sincronizar_nro_creditnote . "\n" . mysqli_error($conn);
    }
  }


  function syncronize_status($conn, $printer_status)
  {

    // Check connection
    if ($conn->connect_error) {
      die("(syncronize_status) Connection failed: " . $conn->connect_error);
    }

    if ($printer_status ==  null || $printer_status ==  "") {
      die("dato vital vacio (syncronize_status) (printer_status)\n");
    }


    // cambiar los datos fiscales de las facturas.
    $query_sincronizar_estados_impresora = 
    "INSERT INTO printer_status_data ( status_code, cashregister_user_number, total_daily_invoices, last_invoice_number, daily_invoice_quantity, last_debitnote_number, daily_debitnote_quantity, last_creditnote_number, daily_creditnote_quantity, last_nonfiscal_document_number, daily_nonfiscal_documents_quantity, daily_fiscal_memory_reports, daily_closes_counter, registered_enterprise_fiscal_document_number, printer_register_number, printer_current_time, printer_current_date, update_date) 
    VALUES('".$printer_status[0]."', '".$printer_status[1]."', '".$printer_status[2]."', '".$printer_status[3]."', '".$printer_status[4]."', '".$printer_status[5]."', '".$printer_status[6]."', '".$printer_status[7]."', '".$printer_status[8]."', '".$printer_status[9]."', '".$printer_status[10]."', '".$printer_status[11]."', '".$printer_status[12]."', '".$printer_status[13]."', '".$printer_status[14]."', '".$printer_status[15]."', '".$printer_status[16]."', NOW());";

    $cambiar_registro = $conn->prepare($query_sincronizar_estados_impresora);

    if ($cambiar_registro->execute()) {
      echo "Se ha GUARDADO LOS ESTADOS EXTRAIDOS DE LA IMPRESORA\n";
    } else {
      echo "( Error: GUARDANDO LOS ESTADOS EXTRAIDOS DE LA IMPRESORA)  " . $query_sincronizar_estados_impresora . "\n" . mysqli_error($conn);
    }
  }
}
