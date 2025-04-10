<?php

include_once("TfhkaPHP.php");
include_once("interpreter.php");
include_once("Utils.php");
include_once("DatabaseBridge.php");

$itObj = new Tfhka(); // printer api

class debitnoteHandler
{

  function get_debitnote_info($conn, $debitnote_id)
  {

    // Check connection
    if ($conn->connect_error) {
      die("(get_debitnote_info) Connection failed: " . $conn->connect_error);
    }

    if ($debitnote_id == null || $debitnote_id == "") {
      die("dato vital vacio (get_debitnote_info)\n");
    }

    $query_info_debitnote = "SELECT * FROM dbo_finance_debitnotes WHERE id = " . $debitnote_id . ";";
    $info_debitnote = null;
    $info_debitnote = $conn->query($query_info_debitnote);

    if ($info_debitnote->num_rows == 0) {
      die("Nota de debito con ese id no existe");
    }

    return $info_debitnote;
  }


  function get_info_fiscal($conn, $debitnote_id)
  {

    // Check connection
    if ($conn->connect_error) {
      die("(get_debitnote_info) Connection failed: " . $conn->connect_error);
    }

    if ($debitnote_id == null || $debitnote_id == "") {
      die("dato vital vacio (get_debitnote_info)\n");
    }

    // detalles fiscales del documento
    $query_info_fiscal_debitnote =
      "SELECT
        IFNULL(dbo_finance_debitnotes.debitnote_number,'na') as debitnote_number,
        IFNULL(dbo_administration_invoices.invoice_number,'na') as invoice_number,
        IFNULL(dbo_administration_invoices.fiscal,'0') as fiscal,
        dbo_finance_debitnotes.observations,
        dbo_finance_debitnotes.debitnote_amount,
        DATE_FORMAT( dbo_finance_debitnotes.createdAt, '%d-%m-%Y') as createdAt,
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
      dbo_finance_debitnotes
      left join dbo_sales_clients on dbo_finance_debitnotes.client_id = dbo_sales_clients.id
      left join dbo_config_identifications_types on dbo_sales_clients.identification_type_id = dbo_config_identifications_types.id
      left join dbo_system_users on dbo_finance_debitnotes.user_id = dbo_system_users.id
      left join dbo_administration_invoices on dbo_finance_debitnotes.invoice_id = dbo_administration_invoices.id
      where dbo_finance_debitnotes.id =" . $debitnote_id . ";";

    var_dump("query a revisarsisimo debito");
    var_dump($query_info_fiscal_debitnote);

    $info_fiscal_debitnote = $conn->query($query_info_fiscal_debitnote);

    if ($info_fiscal_debitnote->num_rows == 0) {
      die("nota de debito con info fiscal con ese id no existe, o faltan datos");
    }

    return $info_fiscal_debitnote;
  }

  function get_debitnote_items($conn, $debitnote_id, $tipo_de_nota, $total)
  {

    // Check connection
    if ($conn->connect_error) {
      die("(get_debitnote_items) Connection failed: " . $conn->connect_error);
    }

    if ($debitnote_id == null || $debitnote_id == "") {
      die("dato vital vacio (get_debitnote_items)\n");
    }

    // DE MOMENTO LAS NOTAS DE DEBITO NO POSEEN ITEMS, POR LO TANTO, ESTO ES INNCESESARIO


    // inicializo una instancia de interprete para el tipo de doc.
    // ...(hago una instancia del interprete del tipo de doc)
    $interpreter = new interpreter();
    $interpreter_nofiscal = new interpreter_nofiscal();


    $debitnote_en_contruccion = array();
    $index_counter = 0;
    $index_inverse_counter = 0;


    $query_items_debitnote =
      "SELECT
        dbo_finance_debitnotes_items.debitnote_id,
        dbo_storage_products.`code`,
        dbo_storage_products.description,
        dbo_administration_invoices_items.price as check_price,
        dbo_administration_invoices_items_prices.unit_price_after_discount as price,
        dbo_finance_debitnotes_items.net_amount,
        dbo_finance_debitnotes_items.product_quantity,
        dbo_finance_debitnotes_items.observations,
        dbo_config_taxes.tax_code,
        dbo_config_taxes.observation as tax_observation
      FROM `dbo_finance_debitnotes_items`
      join dbo_finance_debitnotes on dbo_finance_debitnotes.id = dbo_finance_debitnotes_items.debitnote_id
      join dbo_administration_invoices on dbo_administration_invoices.id = dbo_finance_debitnotes.invoice_id
      join dbo_storage_products on dbo_finance_debitnotes_items.product_id = dbo_storage_products.id
      join dbo_administration_invoices_items on dbo_administration_invoices_items.product_id = dbo_finance_debitnotes_items.product_id and dbo_administration_invoices_items.invoice_id = dbo_finance_debitnotes.invoice_id
      join dbo_config_taxes on dbo_config_taxes.id = dbo_administration_invoices_items.tax_id
      join dbo_administration_invoices_items_prices on dbo_administration_invoices_items.id = dbo_administration_invoices_items_prices.invoice_item_id 
      and dbo_administration_invoices_items_prices.currency_id = 2
      WHERE 	dbo_finance_debitnotes_items.debitnote_id = " . $debitnote_id . ";
    ";

    $items_nota_debito = $conn->query($query_items_debitnote);
    var_dump($query_items_debitnote);

    if (!($items_nota_debito->num_rows > 0)) {
      var_dump("no hay items asociados a esa nota de debito");
      return "false";
    } else {

      // output data of each row
      while ($item = $items_nota_debito->fetch_assoc()) {
        echo "\n";
        echo "price: " . $item["price"] . " - quantity: " . $item["product_quantity"] . ", description " . $item["description"] . ", observation: " . $item["observations"];
        echo "\n";

        // proximamente al interpreter
        // .. el tax rate, deberia pasarse en texto (ya, pero se llama observation en el query, esta en string)
        // $tasa="", $precio = "", $cant = "", $desc = ""
        // $debitnote_en_contruccion[$index_counter] = $interpreter->translateLineCommentCredito($item["observations"])."\n";
        // $index_counter++;
        // $debitnote_en_contruccion[$index_counter] = $interpreter->translateLinedebito($item["tax_observation"],$item["price"],$item["product_quantity"],$item["description"])."\n";
        // $index_counter++;


        if ($tipo_de_nota == "fiscal") {
          // proximamente al interpreter
          // .. el tax rate, deberia pasarse en texto (ya, pero se llama observation en el query, esta en string)
          // $tasa="", $precio = "", $cant = "", $desc = ""
          $debitnote_en_contruccion[$index_counter] = $interpreter->translateLinedebito($item["tax_code"], $item["price"], $item["product_quantity"], $item["description"]) . "\n";
          $index_counter++;
        } else {
          // el interpreter en los no fiscales genera 2 lineas separadas, si es un item de de  mas de 2 items

          // // .. el tax rate, deberia pasarse en texto (ya, pero se llama observation en el query, esta en string)
          // $tasa="", $precio = "", $cant = "", $desc = ""
          if ($item["product_quantity"] == "1") {
            $debitnote_en_contruccion[$index_counter] = $interpreter_nofiscal->translateLinedebito($item["tax_observation"], $item["net_amount"], $item["product_quantity"], $item["description"]) . "\n";
            $index_counter++;
          } else {
            // $precio = "", $cant = ""
            $debitnote_en_contruccion[$index_counter] = $interpreter_nofiscal->translateLinePrice($item["tax_observation"], $item["net_amount"], $item["product_quantity"], $item["description"]) . "\n";
            $index_counter++;
            // $tasa="", $desc = ""
            $debitnote_en_contruccion[$index_counter] = $interpreter_nofiscal->translateLineDesc($item["tax_observation"], $item["net_amount"], $item["product_quantity"], $item["description"]) . "\n";
            $index_counter++;
          }
        }
      }
    }


    if ($tipo_de_nota == "fiscal") {
      //cierre de factura (viene despues de los items)
      $debitnote_en_contruccion[$index_counter] = "101" . "\n";

      if (D_IGTF == true) {
        $index_counter++; // sino sobre escribo el pago con 199
        $debitnote_en_contruccion[$index_counter] = "199";
      }
    } else {
      $debitnote_en_contruccion[$index_counter] = $interpreter_nofiscal->separador();
      $index_counter++;

      // total
      $debitnote_en_contruccion[$index_counter] = $interpreter_nofiscal->translateFinalTotal($total) . "\n";
      $index_counter++;

      $debitnote_en_contruccion[$index_counter] = "810";
    }

    return $debitnote_en_contruccion;
  }


  function printDebitnote($conn, $documento_imprimiendo)
  {

    // Check connection
    if ($conn->connect_error) {
      die("(debitnoteHandler) Connection failed: " . $conn->connect_error);
    }


    if ($documento_imprimiendo == null) {
      die("dato vital vacio (debitnoteHandler)\n");
    }

    // tomo el id de la nota de debito
    $debitnote_id = $documento_imprimiendo["document_id"];

    // detalles de documento
    $info_debitnote = $this->get_debitnote_info($conn, $debitnote_id);

    // objeto de los datos de la nota de debito.
    $nota_debito_actual = $info_debitnote->fetch_assoc();

    // informacion fiscal
    $info_fiscal_nota_debito = $this->get_info_fiscal($conn, $debitnote_id);



    // objeto informacion fiscal nota de debito
    $nota_debito_actual = $info_fiscal_nota_debito->fetch_assoc();

    // info de nota de debito
    $numero_debitnote = $nota_debito_actual["debitnote_number"];
    $amount = $nota_debito_actual["debitnote_amount"];

    // probando
    $total = $amount;

    // nombre Cajero
    $nombre_cajero = $documento_imprimiendo["cashier_name"];

    // tipo
    $es_fiscal = $nota_debito_actual["fiscal"];
    $tipo_de_nota = ($es_fiscal == "1") ? "fiscal" : "no fiscal";


    echo "\n";
    echo "el documento a imprimir es la nota de debito de numero: " . $numero_debitnote . ", por cajero " . $nombre_cajero . "\n ";
    echo "\n";

    // inicializo una instancia de interprete para el tipo de doc.
    // ...(hago una instancia del interprete del tipo de doc)
    $interpreter = new interpreter();

    // counter for translation
    $debitnote_en_contruccion = array();
    $index_counter = 0;
    $index_inverse_counter = 0;

    // consultar informacion fiscal de la nota de debito antes de armarla
    $infoFiscalTraducida = $interpreter->translateFiscalInfoArrayDebitnote($nota_debito_actual);

    // arreglo de los items de la nota de debito
    // de momento las notas de debito no poseen items por lo tanto... estos traducen en 1 solo
    // $items_factura = $this->get_debitnote_items($conn ,$debitnote_id);
    $items_nota = "false";


    // en caso de que la nota de debito no tenia items, esto aplica
    if ($items_nota == "false") {
      $items_nota_extra = array();
      $items_nota_extra[1] = $interpreter->translateLineDebito("Sin IVA", $amount, 1, "Deuda") . "\n";

      $cierre = array();
      $cierre[2] = "101" . "\n";

      //cierre de factura (viene despues de los items)
      // $factura_en_contruccion[$index_counter] = "105";
      if (D_IGTF == true) {
        $cierre[3] = "199";
      }

      $debitnote_en_contruccion = $infoFiscalTraducida + $items_nota_extra + $cierre;
    } else {
      // concateno la informacion fiscal a la de los items de la nota de debito
      $debitnote_en_contruccion = $infoFiscalTraducida + $items_nota;

      if (D_IGTF == true) {
        $cierre = array();
        $cierre[0] = "199";
        $debitnote_en_contruccion = $infoFiscalTraducida + $items_nota + $cierre;
      }

    }

    //cierre de nota de debito (lo coloque en los items de una vez)
    //.. si quiero luego colocar pie de nota de debito aqui lo puedo hacer con el size de $items_nota de debito + 1 como indice y sumo
    // $debitnote_en_contruccion[$index_counter] = "101";

    echo "\n";
    var_dump($debitnote_en_contruccion);
    echo "\n";

    // creo el archivo de la nota de debito y lo mando a imprimir
    $Utils = new Utils();
    $filename = "ND/NotadeDebito" . $numero_debitnote . ".txt";
    $file = $Utils->printFileFromArray($debitnote_en_contruccion, $filename);

    // $this->cierre_documento();

    $respuesta_impresora = $Utils->printFile($filename);
    // linea para emular impresion exitosa.
    // $respuesta_impresora = "true";

    $respuesta_status = $Utils->system_status();


    if ($respuesta_impresora == "true") {
      return "true";
    } else {
      echo "la impresora fallo... (hay que colocar los errores en log)\n";
      return "false";
    }
  }


}