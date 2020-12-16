<?php

  include_once ("TfhkaPHP.php"); 
  include_once ("interpreter.php"); 
  include_once ("interpreter_nofiscal.php"); 
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
        dbo_administration_invoices.saleorder_number,
        dbo_administration_invoices.tax_id,
        dbo_administration_invoices.credit,
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
        dbo_system_users.rol_id,
        dbo_administration_invoices_all_currencies.total as reference_dolar
      FROM 
        dbo_administration_invoices
      join dbo_administration_invoices_all_currencies on (dbo_administration_invoices.id = dbo_administration_invoices_all_currencies.invoice_id and dbo_administration_invoices_all_currencies.currency_id = 1) 
      join dbo_sales_clients on dbo_administration_invoices.client_id = dbo_sales_clients.id
      join dbo_config_identifications_types on dbo_sales_clients.identification_type_id = dbo_config_identifications_types.id
      join dbo_system_users on dbo_administration_invoices.user_id = dbo_system_users.id
      WHERE dbo_administration_invoices.id = ".$invoice_id.";";

    $info_fiscal_factura = $conn->query($query_info_fiscal_factura);

    if ($info_fiscal_factura->num_rows == 0) { die("factura con info fiscal con ese id no existe"); }

    return  $info_fiscal_factura;

  }

  function get_invoice_items($conn, $invoice_id,$tipo_de_factura, $subtotal, $tax, $total ){

    // Check connection
    if ($conn->connect_error) {
      die("(get_invoice_items) Connection failed: " . $conn->connect_error);
    }
    
    if($invoice_id ==  null || $invoice_id ==  "" || $tipo_de_factura ==""){
      die("dato vital vacio (get_invoice_items)\n");
    }
    
    // inicializo una instancia de interprete para el tipo de doc.
    // ...(hago una instancia del interprete del tipo de doc)
    $interpreter = new interpreter();
    $interpreter_nofiscal = new interpreter_nofiscal();

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
        ROUND(dbo_administration_invoices_items.tax_base / dbo_administration_invoices_items.quantity, 2) as real_base_check,
        dbo_administration_invoices_items_prices.unit_price_after_discount as real_base,
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
      join dbo_administration_invoices_items_prices on dbo_administration_invoices_items.id = dbo_administration_invoices_items_prices.invoice_item_id 
      and dbo_administration_invoices_items_prices.currency_id = 2
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

      if($tipo_de_factura == "fiscal"){
        // proximamente al interpreter
        // .. el tax rate, deberia pasarse en texto (ya, pero se llama observation en el query, esta en string)
        // $tasa="", $precio = "", $cant = "", $desc = ""
        $factura_en_contruccion[$index_counter] = $interpreter->translateLine($item["observation"],$item["real_base"],$item["quantity"],$item["description"])."\n";
        $index_counter++;
      }else{
        // el interpreter en los no fiscales genera 2 lineas separadas, si es un item de de  mas de 2 items

        // // .. el tax rate, deberia pasarse en texto (ya, pero se llama observation en el query, esta en string)
        // $tasa="", $precio = "", $cant = "", $desc = ""
        if($item["quantity"] == "1"){
          $factura_en_contruccion[$index_counter] = $interpreter_nofiscal->translateLine($item["observation"],$item["real_base"],$item["quantity"],$item["description"])."\n";
          $index_counter++;
        }else{
          // $precio = "", $cant = ""
          $factura_en_contruccion[$index_counter] = $interpreter_nofiscal->translateLinePrice($item["observation"],$item["real_base"],$item["quantity"],$item["description"])."\n";
          $index_counter++;
          // $tasa="", $desc = ""
          $factura_en_contruccion[$index_counter] = $interpreter_nofiscal->translateLineDesc($item["observation"],$item["real_base"],$item["quantity"],$item["description"])."\n";
          $index_counter++;

        }


      }

    }

    if($tipo_de_factura == "fiscal"){
      //cierre de factura (viene despues de los items)
      $factura_en_contruccion[$index_counter] = "101";
    }else{
      //cierre de factura no fiscal (viene despues de los items)
      $factura_en_contruccion[$index_counter] = $interpreter_nofiscal->separador();
      $index_counter++;

      // // subtotal
      // $factura_en_contruccion[$index_counter] = $interpreter_nofiscal->translateSubtotal($subtotal)."\n";
      // $index_counter++;
      
      // ########################################################################
      // esto dejo de ser valido, ya que el impuesto no es aplicable a notas de venta
      // hay que coordinar para que el calculo total de la factura refleje los montos de 
      // sistema, yo poner el monto subtotal aqui, es un malentendido y confunde 
      // respecto a la info arrojada por el sistema.
      // ##########################################################################

      // // iva
      // $factura_en_contruccion[$index_counter] = $interpreter_nofiscal->translateTax($tax)."\n";
      // $index_counter++;

      // $factura_en_contruccion[$index_counter] =  $interpreter_nofiscal->separador();
      // $index_counter++;

      // // total
      $factura_en_contruccion[$index_counter] = $interpreter_nofiscal->translateFinalTotal($total)."\n";
      $index_counter++;

      $factura_en_contruccion[$index_counter] = "810";
    }

    return  $factura_en_contruccion;

  }



  function printInvoice($conn, $documento_imprimiendo ){

    // Check connection
    if ($conn->connect_error) {
      die("(printInvoice) Connection failed: " . $conn->connect_error);
    }
    

    if($documento_imprimiendo ==  null){
      die("dato vital vacio (printInvoice)\n");
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


    // info de factura
    $numero_factura = $factura_actual["invoice_number"];
    $subtotal = $factura_actual["subtotal"];
    $tax = $factura_actual["tax"];
    $total = $factura_actual["total"];

    // nombre Cajero
    $nombre_cajero = $documento_imprimiendo["cashier_name"];

    // tipo
    $es_fiscal = $factura_actual["fiscal"];
    $tipo_de_factura = ($es_fiscal == "1")? "fiscal":"no fiscal";

    echo "\n";
    echo "el documento a imprimir es la factura ".$tipo_de_factura." de numero: " . $numero_factura .", por cajero ". $nombre_cajero. "\n ";
    echo "\n";

    // inicializo una instancia de interprete para el tipo de doc.
    // ...(hago una instancia del interprete del tipo de doc)
    $interpreter = new interpreter();
    $interpreter_nofiscal = new interpreter_nofiscal();

    // counter for translation
    $factura_en_contruccion = array();
    $index_counter = 0;
    $index_inverse_counter = 0;

    if($tipo_de_factura == "fiscal"){
      // consultar informacion fiscal de la factura antes de armarla
      $infoFiscalTraducida = $interpreter->translateFiscalInfoArray($factura_fiscal_actual);

      // arreglo de los items de la factura
      $items_factura = $this->get_invoice_items($conn ,$invoice_id, $tipo_de_factura, $subtotal, $tax, $total);

    }else{
      // consultar informacion fiscal de la factura antes de armarla
      $infoFiscalTraducida = $interpreter_nofiscal->translateFiscalInfoArray($factura_fiscal_actual);

      // arreglo de los items de la factura
      $items_factura = $this->get_invoice_items($conn ,$invoice_id, $tipo_de_factura, $subtotal, $tax, $total);
      
    }

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
    // linea para emular impresion exitosa.
    // $respuesta_impresora = "true";

    if($respuesta_impresora == "true"){

      return "true";

    }else{
      echo "la impresora fallo... (hay que colocar los errores en log)\n";
      return "false";

    }


  }




}
?>
