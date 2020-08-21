<?php

  include_once ("TfhkaPHP.php"); 
  include_once ("interpreter.php"); 
  include_once ("Utils.php"); 
  include_once ("DatabaseBridge.php"); 

  $itObj = new Tfhka(); // printer api

class creditnoteHandler
{
  
  function get_creditnote_info($conn, $creditnote_id ){

    // Check connection
    if ($conn->connect_error) {
      die("(get_creditnote_info) Connection failed: " . $conn->connect_error);
    }
    
    if($creditnote_id ==  null || $creditnote_id ==  "" ){
      die("dato vital vacio (get_creditnote_info)\n");
    }

    $query_info_creditnote = "SELECT * FROM dbo_finance_creditnotes WHERE id = ".$creditnote_id.";";
    $info_creditnote = null;
    $info_creditnote = $conn->query($query_info_creditnote);

    if ($info_creditnote->num_rows == 0) { die("Nota de credito con ese id no existe"); }

    return  $info_creditnote;

  }

  
  function get_info_fiscal($conn, $creditnote_id ){

    // Check connection
    if ($conn->connect_error) {
      die("(get_creditnote_info) Connection failed: " . $conn->connect_error);
    }
    
    if($creditnote_id ==  null || $creditnote_id ==  "" ){
      die("dato vital vacio (get_creditnote_info)\n");
    }

    // detalles fiscales del documento
    $query_info_fiscal_creditnote = 
      "SELECT
        dbo_finance_creditnotes.creditnote_number,
        dbo_finance_creditnotes.observations,
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
        dbo_system_users.rol_id
      FROM
        dbo_finance_creditnotes
      left join dbo_sales_clients on dbo_finance_creditnotes.client_id = dbo_sales_clients.id
      left join dbo_config_identifications_types on dbo_sales_clients.identification_type_id = dbo_config_identifications_types.id
      left join dbo_system_users on dbo_finance_creditnotes.user_id = dbo_system_users.id
      where dbo_finance_creditnotes.id =".$creditnote_id.";";

    var_dump($query_info_fiscal_creditnote);

    $info_fiscal_creditnote = $conn->query($query_info_fiscal_creditnote);

    if ($info_fiscal_creditnote->num_rows == 0) { die("nota de credito con info fiscal con ese id no existe, o faltan datos"); }

    return  $info_fiscal_creditnote;

  }

  function get_creditnote_items($conn, $creditnote_id ){

    // Check connection
    if ($conn->connect_error) {
      die("(get_creditnote_items) Connection failed: " . $conn->connect_error);
    }
    
    if($creditnote_id ==  null || $creditnote_id ==  "" ){
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
        dbo_finance_creditnotes_items.net_amount,
        dbo_finance_creditnotes_items.product_quantity,
        dbo_finance_creditnotes_items.observations,
        dbo_storage_products.`code`,
        dbo_storage_products.description,
      FROM `dbo_finance_creditnotes_items`
      join dbo_storage_products on dbo_finance_creditnotes_items.product_id = dbo_storage_products.id
      WHERE 	dbo_finance_creditnotes_items.creditnote_id = " .$creditnote_id.";
    ";

    $items_factura = $conn->query($query_items_creditnote);

    if (!($items_factura->num_rows > 0)) {
      var_dump("no hay items asociados a esa nota de credito");
    }else{

      // output data of each row
      while($item = $items_factura->fetch_assoc()) {
        // echo "\n";
        // echo "price: " . $item["price"]. " - quantity: " . $item["quantity"]. ", description " . $item["description"];
        // echo "\n";

          // proximamente al interpreter
          // .. el tax rate, deberia pasarse en texto (ya, pero se llama observation en el query, esta en string)
          // $tasa="", $precio = "", $cant = "", $desc = ""
          $creditnote_en_contruccion[$index_counter] = $interpreter->translateLineCredito($item["observations"],$item["net_amount"],$item["product_quantity"],$item["description"])."\n";
          $index_counter++;

      }

    }

    //cierre de factura (viene despues de los items)
    $creditnote_en_contruccion[$index_counter] = "101";

    return  $creditnote_en_contruccion;

  }


  function printCreditnote($conn, $documento_imprimiendo ){

    // Check connection
    if ($conn->connect_error) {
      die("(creditnoteHandler) Connection failed: " . $conn->connect_error);
    }
    

    if($documento_imprimiendo ==  null){
      die("dato vital vacio (creditnoteHandler)\n");
    }

    // tomo el id de la factura
    $creditnote_id = $documento_imprimiendo["document_id"];

    // detalles de documento
    $info_creditnote = $this->get_creditnote_info($conn,$creditnote_id);

    // objeto de los datos de la factura.
    $factura_actual = $info_creditnote->fetch_assoc();

    // informacion fiscal
    $info_fiscal_factura =  $this->get_info_fiscal($conn,$creditnote_id);

    // objeto informacion fiscal factura
    $factura_fiscal_actual = $info_fiscal_factura->fetch_assoc();


    // info de factura
    $numero_creditnote = $factura_actual["creditnote_number"];
    $subtotal = $factura_actual["subtotal"]; // --- maybe
    $tax = $factura_actual["tax"]; // --- maybe
    $amount = $factura_actual["amount"];

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
    $infoFiscalTraducida = $interpreter->translateFiscalInfoArrayCreditnote($factura_fiscal_actual);

    // arreglo de los items de la factura
    $items_factura = $this->get_creditnote_items($conn ,$creditnote_id);

    // concateno la informacion fiscal a la de los items de la factura
    $creditnote_en_contruccion = $infoFiscalTraducida + $items_factura;

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
    
    if($respuesta_impresora == "true"){

      return "true";

    }else{
      echo "la impresora fallo... (hay que colocar los errores en log)\n";
      return "false";

    }


  }




}
?>
