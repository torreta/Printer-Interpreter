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
        concat(truncate(dbo_administration_invoices_all_currencies.total,0),'C', SUBSTR(cast(dbo_administration_invoices_all_currencies.total - truncate(dbo_administration_invoices_all_currencies.total,0) as char),3)) as reference_dolar
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
        dbo_administration_invoices_items_prices.total_price_without_discount as real_base_no_discount,
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

      // para dejar constancia, antes era real base lo que se pasaba por parametros

      if($tipo_de_factura == "fiscal"){
        // proximamente al interpreter
        // .. el tax rate, deberia pasarse en texto (ya, pero se llama observation en el query, esta en string)
        // $tasa="", $precio = "", $cant = "", $desc = ""
        $factura_en_contruccion[$index_counter] = $interpreter->translateLine($item["observation"],$item["real_base_no_discount"],$item["quantity"],$item["description"])."\n";
        $index_counter++;
      }else{
        // el interpreter en los no fiscales genera 2 lineas separadas, si es un item de de  mas de 2 items

        // // .. el tax rate, deberia pasarse en texto (ya, pero se llama observation en el query, esta en string)
        // $tasa="", $precio = "", $cant = "", $desc = ""
        if($item["quantity"] == "1"){
          $factura_en_contruccion[$index_counter] = $interpreter_nofiscal->translateLine($item["observation"],$item["real_base_no_discount"],$item["quantity"],$item["description"])."\n";
          $index_counter++;
        }else{
          // $precio = "", $cant = ""
          $factura_en_contruccion[$index_counter] = $interpreter_nofiscal->translateLinePrice($item["observation"],$item["real_base_no_discount"],$item["quantity"],$item["description"])."\n";
          $index_counter++;
          // $tasa="", $desc = ""
          $factura_en_contruccion[$index_counter] = $interpreter_nofiscal->translateLineDesc($item["observation"],$item["real_base_no_discount"],$item["quantity"],$item["description"])."\n";
          $index_counter++;

        }


      }

    }


    // AQUI TENGO QUE PONER LO QUE SEA QUE SE TIENE DE DESCUENTO SOBRE LA FACTURA BASADO EN LOS ITEMS
    if($tipo_de_factura == "fiscal"){

      $query_descuento_factura = 
        "SELECT
        dbo_administration_invoices_items.id,
        dbo_config_currencies.`name`	,
        sum(dbo_administration_invoices_items_prices.discount) as discount_total
      FROM `dbo_administration_invoices_items`
      join dbo_config_taxes on dbo_administration_invoices_items.tax_id = dbo_config_taxes.id
      join dbo_config_exchange_rates on dbo_administration_invoices_items.exchange_rate_id = dbo_config_exchange_rates.id
      join dbo_config_currencies on dbo_config_exchange_rates.currency_id = dbo_config_currencies.id
      join dbo_administration_invoices_items_prices on dbo_administration_invoices_items.id = dbo_administration_invoices_items_prices.invoice_item_id 
      and dbo_administration_invoices_items_prices.currency_id = 2
      WHERE 	dbo_administration_invoices_items.invoice_id = " .$invoice_id;      

      var_dump($query_descuento_factura);

      $items_descuentos = $conn->query($query_descuento_factura);

      var_dump($items_descuentos);

        
      // tomo el descuento total
      while($descuento = $items_descuentos->fetch_assoc()) {
        // verifico que el descuento sea mayor a cero para reflejarlo
        if (floatval($descuento["discount_total"]) > 0 ){
          $factura_en_contruccion[$index_counter] = $interpreter->translateLineDescuento($descuento["discount_total"])."\n";
          $index_counter++;
        }
      
      }

    }



    if($tipo_de_factura == "fiscal"){

      $query_pagos_factura = 
        "SELECT
          dbo_administration_invoices.id as invoice_id,
          dbo_administration_invoices.invoice_number,
          dbo_administration_invoices.saleorder_number,
          dbo_administration_invoices.total as invoice_total,
          dbo_administration_invoices.real_total as invoice_real_total,
          dbo_finance_payments.amount as payment_amount,
          ROUND(if(dbo_config_currencies.name = 'Dolar',dbo_finance_payments.amount * dbo_config_exchange_rates.exchange_rate, dbo_finance_payments.amount),2)   as payment_translated,
          dbo_config_exchange_rates.exchange_rate as exchange_rate ,
          dbo_finance_payment_types.name as payment_type,
          dbo_config_currencies.name as payment_currency,
          dbo_config_currencies.abbreviation as currency_sign
        FROM dbo_finance_payments
        join dbo_administration_invoices on dbo_administration_invoices.id = dbo_finance_payments.invoice_id 
        join dbo_config_exchange_rates on dbo_administration_invoices.exchange_rate_id = dbo_config_exchange_rates.id 
        join dbo_finance_payment_types on dbo_finance_payment_types.id = dbo_finance_payments.payment_type_id 
        join dbo_config_currencies on dbo_config_currencies.id = dbo_finance_payments.currency_id 
        where dbo_administration_invoices.id = " .$invoice_id;      

      var_dump($query_pagos_factura);

      $items_pagos = $conn->query($query_pagos_factura);

      var_dump($items_pagos);

      // Ningun Pago registrado asi que lo tomo a credito
      if ($items_pagos->num_rows == 0) {
        echo("no hay pagos asociados esa factura \n ");
        $factura_en_contruccion[$index_counter] = $interpreter->translateLinePagoTotal("Credito")."\n"; // segun manual
      }

      // Ningun Pago registrado asi que lo tomo a credito
      if ($items_pagos->num_rows == 1) {
        var_dump($items_pagos);
        echo("1 solo pago asociado a esa factura \n ");
        $pago = $items_pagos->fetch_assoc();
        $factura_en_contruccion[$index_counter] = $interpreter->translateLinePagoTotal($pago["payment_type"])."\n";
      }

      //  MAS DE UN PAGO
      // para acumular los montos de los pagos y verificar que no nos pasemos
      $sumador_de_pagos = 0;

      if ($items_pagos->num_rows > 1) {
        // tomo la cantidad de pagos que quedan
        $counter_pagos =  $items_pagos->num_rows;
        // output data of each row
        while($pago = $items_pagos->fetch_assoc()) {

          if($counter_pagos == 1){
            $factura_en_contruccion[$index_counter] = $interpreter->translateLinePagoTotal($pago["payment_type"])."\n";

          }else{
            $sumador_de_pagos =  $sumador_de_pagos + floatval($pago["payment_translated"]);
            
            // verificando que mi pago no exceda el total de factura (sino, debo romper el ciclo y colocar ese pago como pago total)
            if (floatval($pago["invoice_real_total"]) < $sumador_de_pagos ){
              $factura_en_contruccion[$index_counter] = $interpreter->translateLinePagoTotal($pago["payment_type"])."\n";
              break;
            }
            
            $factura_en_contruccion[$index_counter] = $interpreter->translateLinePagoParcial($pago["payment_type"],$pago["payment_translated"])."\n";
            $index_counter++;
            // voy restando los pagos parciales para asegurarme de que hago el ultimo
            // pago como total
            $counter_pagos = $counter_pagos - 1;

          }
            
        }

      }

      //cierre de factura (viene despues de los items)
      // $factura_en_contruccion[$index_counter] = "105";
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

      // AQUI AHORA TOCA METODOS DE PAGO PARA LAS NO FISCALES
      // ESO incluye su propio traductor de linea

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
    $filename = "FA/Factura".$numero_factura.".txt";	
    $file = $Utils->printFileFromArray($factura_en_contruccion, $filename);
    
    // en caso de que se necesite imprimir o sacar algo de la cola que 
    // se haya quedado pegada. el falso se puede usar para saltar alguno.
    // (en este caso el falso es para poder probar solo con consola)
    // pues los archivos deberia crearlos bien formateados de todos modos.
    $respuesta_impresora = $Utils->printFile($filename);
    // $respuesta_impresora = $Utils->printFileFalso($filename);

    // linea para emular impresion exitosa.
    // $respuesta_impresora = "true";

    if($respuesta_impresora == "true"){

      return "true";

    }else{
      echo "la impresora fallo... (hay que colocar los errores en log)\n";
      return "false";

    }


  }


  function printCopy($conn, $documento_imprimiendo ){

    // Check connection
    if ($conn->connect_error) {
      die("(printInvoice) Connection failed: " . $conn->connect_error);
    }
    

    if($documento_imprimiendo ==  null){
      die("dato vital vacio (printInvoiceCopy)\n");
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
    $tipo_de_factura = "no fiscal";

    echo "\n";
    echo "el documento a imprimir es la copia de la factura ".$tipo_de_factura." de numero: " . $numero_factura .", por cajero ". $nombre_cajero. "\n ";
    echo "\n";

    // inicializo una instancia de interprete para el tipo de doc.
    // ...(hago una instancia del interprete del tipo de doc)
    $interpreter_nofiscal = new interpreter_nofiscal();

    // counter for translation
    $factura_en_contruccion = array();
    $index_counter = 0;
    $index_inverse_counter = 0;

    // consultar informacion fiscal de la factura antes de armarla
    $infoFiscalTraducida = $interpreter_nofiscal->translateFiscalInfoArrayCopy($factura_fiscal_actual);

    // arreglo de los items de la factura
    $items_factura = $this->get_invoice_items($conn ,$invoice_id, $tipo_de_factura, $subtotal, $tax, $total);

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
    $filename = "FA/Factura".$numero_factura.".txt";	
    $file = $Utils->printFileFromArray($factura_en_contruccion, $filename);
    
    // en caso de que se necesite imprimir o sacar algo de la cola que 
    // se haya quedado pegada. el falso se puede usar para saltar alguno.
    // (en este caso el falso es para poder probar solo con consola)
    // pues los archivos deberia crearlos bien formateados de todos modos.
    $respuesta_impresora = $Utils->printFile($filename);
    // $respuesta_impresora = $Utils->printFileFalso($filename);

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
