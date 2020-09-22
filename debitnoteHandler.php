<?php

  include_once ("TfhkaPHP.php"); 
  include_once ("interpreter.php"); 
  include_once ("Utils.php"); 
  include_once ("DatabaseBridge.php"); 

  $itObj = new Tfhka(); // printer api

class debitnoteHandler
{
  
  function get_debitnote_info($conn, $debitnote_id ){

    // Check connection
    if ($conn->connect_error) {
      die("(get_debitnote_info) Connection failed: " . $conn->connect_error);
    }
    
    if($debitnote_id ==  null || $debitnote_id ==  "" ){
      die("dato vital vacio (get_debitnote_info)\n");
    }

    $query_info_debitnote = "SELECT * FROM dbo_finance_debitnotes WHERE id = ".$debitnote_id.";";
    $info_debitnote = null;
    $info_debitnote = $conn->query($query_info_debitnote);

    if ($info_debitnote->num_rows == 0) { die("Nota de debito con ese id no existe"); }

    return  $info_debitnote;

  }

  
  function get_info_fiscal($conn, $debitnote_id ){

    // Check connection
    if ($conn->connect_error) {
      die("(get_debitnote_info) Connection failed: " . $conn->connect_error);
    }
    
    if($debitnote_id ==  null || $debitnote_id ==  "" ){
      die("dato vital vacio (get_debitnote_info)\n");
    }

    // detalles fiscales del documento
    $query_info_fiscal_creditnote = 
      "SELECT
        IFNULL(dbo_finance_creditnotes.creditnote_number,'na') as creditnote_number,
        IFNULL(dbo_administration_invoices.invoice_number,'na') as invoice_number,
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
      where dbo_finance_creditnotes.id =".$debitnote_id.";";

    var_dump($query_info_fiscal_creditnote);

    $info_fiscal_creditnote = $conn->query($query_info_fiscal_creditnote);

    if ($info_fiscal_creditnote->num_rows == 0) { die("nota de credito con info fiscal con ese id no existe, o faltan datos"); }

    return  $info_fiscal_creditnote;

  }

  function get_creditnote_items($conn, $debitnote_id ){

    // Check connection
    if ($conn->connect_error) {
      die("(get_creditnote_items) Connection failed: " . $conn->connect_error);
    }
    
    if($debitnote_id ==  null || $debitnote_id ==  "" ){
      die("dato vital vacio (get_creditnote_items)\n");
    }
    
    // inicializo una instancia de interprete para el tipo de doc.
    // ...(hago una instancia del interprete del tipo de doc)
    $interpreter = new interpreter();

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
        dbo_finance_creditnotes_items.product_quantity,
        dbo_finance_creditnotes_items.observations,
        dbo_config_taxes.observation as tax_observation
      FROM `dbo_finance_creditnotes_items`
      join dbo_finance_creditnotes on dbo_finance_creditnotes.id = dbo_finance_creditnotes_items.creditnote_id
      join dbo_administration_invoices on dbo_administration_invoices.id = dbo_finance_creditnotes.invoice_id
      join dbo_storage_products on dbo_finance_creditnotes_items.product_id = dbo_storage_products.id
      join dbo_administration_invoices_items on dbo_administration_invoices_items.product_id = dbo_finance_creditnotes_items.product_id and dbo_administration_invoices_items.invoice_id = dbo_finance_creditnotes.invoice_id
      join dbo_config_taxes on dbo_config_taxes.id = dbo_administration_invoices_items.tax_id
      join dbo_administration_invoices_items_prices on dbo_administration_invoices_items.id = dbo_administration_invoices_items_prices.invoice_item_id 
      and dbo_administration_invoices_items_prices.currency_id = 2
      WHERE 	dbo_finance_creditnotes_items.creditnote_id = " .$debitnote_id.";
    ";

    $items_nota_credito = $conn->query($query_items_creditnote);
    // var_dump($items_nota_credito);

    if (!($items_nota_credito->num_rows > 0)) {
      var_dump("no hay items asociados a esa nota de credito");
      return "false";
    }else{

      // output data of each row
      while($item = $items_nota_credito->fetch_assoc()) {
        echo "\n";
        echo "price: " . $item["price"]. " - quantity: " . $item["product_quantity"]. ", description " . $item["description"]. ", observation: " . $item["observations"];
        echo "\n";

        // proximamente al interpreter
        // .. el tax rate, deberia pasarse en texto (ya, pero se llama observation en el query, esta en string)
        // $tasa="", $precio = "", $cant = "", $desc = ""
        // $creditnote_en_contruccion[$index_counter] = $interpreter->translateLineCommentCredito($item["observations"])."\n";
        // $index_counter++;
        $creditnote_en_contruccion[$index_counter] = $interpreter->translateLineCredito($item["tax_observation"],$item["price"],$item["product_quantity"],$item["description"])."\n";
        $index_counter++;

      }

    }

    //cierre de factura (viene despues de los items)
    $creditnote_en_contruccion[$index_counter] = "101";

    return  $creditnote_en_contruccion;

  }


  function printDebitnote($conn, $documento_imprimiendo ){

    // Check connection
    if ($conn->connect_error) {
      die("(debitnoteHandler) Connection failed: " . $conn->connect_error);
    }
    

    if($documento_imprimiendo ==  null){
      die("dato vital vacio (debitnoteHandler)\n");
    }

    // tomo el id de la factura
    $debitnote_id = $documento_imprimiendo["document_id"];

    // detalles de documento
    $info_creditnote = $this->get_debitnote_info($conn,$debitnote_id);



    // objeto de los datos de la factura.
    $nota_credito_actual = $info_creditnote->fetch_assoc();

    // informacion fiscal
    $info_fiscal_nota_credito =  $this->get_info_fiscal($conn,$debitnote_id);

    // objeto informacion fiscal factura
    $nota_credito_actual = $info_fiscal_nota_credito->fetch_assoc();

    // info de factura
    $numero_creditnote = $nota_credito_actual["creditnote_number"];
    $amount = $nota_credito_actual["creditnote_amount"];

    // nombre Cajero
    $nombre_cajero = $documento_imprimiendo["cashier_name"];


    echo "\n";
    echo "el documento a imprimir es la nota de credito de numero: " . $numero_creditnote .", por cajero ". $nombre_cajero. "\n ";
    echo "\n";

    // inicializo una instancia de interprete para el tipo de doc.
    // ...(hago una instancia del interprete del tipo de doc)
    $interpreter = new interpreter();

    // counter for translation
    $creditnote_en_contruccion = array();
    $index_counter = 0;
    $index_inverse_counter = 0;

    // consultar informacion fiscal de la factura antes de armarla
    $infoFiscalTraducida = $interpreter->translateFiscalInfoArrayCreditnote($nota_credito_actual);

    // arreglo de los items de la factura
    $items_factura = $this->get_creditnote_items($conn ,$debitnote_id);


    // en caso de que la nota de credito no tenia items, esto aplica
    if ($items_factura == "false") {
      $items_factura_extra = array();
      $items_factura_extra[1] = $interpreter->translateLineCredito("Sin IVA",$amount ,1,"Otros")."\n";

      $cierre = array();
      $cierre[2] = "101";
      $creditnote_en_contruccion = $infoFiscalTraducida +  $items_factura_extra + $cierre;

    } else {
      // concateno la informacion fiscal a la de los items de la factura
      $creditnote_en_contruccion = $infoFiscalTraducida + $items_factura;
    }

    //cierre de factura (lo coloque en los items de una vez)
    //.. si quiero luego colocar pie de factura aqui lo puedo hacer con el size de $items_factura + 1 como indice y sumo
    // $creditnote_en_contruccion[$index_counter] = "101";

    echo "\n";
    var_dump($creditnote_en_contruccion) ;
    echo "\n";
    
    // creo el archivo de la factura y lo mando a imprimir
    $Utils = new Utils();
    $filename = "NotadeCredito".$numero_creditnote.".txt";	
    $file = $Utils->printFileFromArray($creditnote_en_contruccion, $filename);
    
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
