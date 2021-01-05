<?php

  include_once ("Utils.php"); 

class interpreter_nofiscal{

  function translateTasa($tasa=""){
    // de momento tengo entendido 4 tipos de tasa
    // (falta copiar de manuak, pero en ejemplo tengo)// (!), ("), (#), ( )
    
    if($tasa == ""){
        echo("valor vacio de tasa \n"); die;
        return false;
    }

    switch ($tasa) {
      case "Sin IVA":
      //echo "Exento\n";
      $comando = " (E)";
      break;
      case "IVA 16%":
      //echo "Tasa 1\n";
      $comando = " (G)";
      break;
      case "Tasa 2":
      //echo "Tasa 2\n";
      $comando = " (R)";
      break;
      case "tasa 3":
      //echo "Tasa 3\n";
      $comando = " (A)";
      break;
      default:
      //echo "Tasa no reconocida\n";
      $comando = false;
      $tasas = ["\"","!","#"," "];
      $tasa = $tasas[array_rand($tasas, 1)];
      $comando = $tasa;
    }
    
    return  $comando;

  }

  
  function translatePrecio( $precio = ""){

    $Utils = new Utils();

    //validaciones de tipo precio
    // Precio del ítem (8 enteros + 2 decimales)
    $enteros = "";   // 8 siempre, cualquier numero + relleno en ceros
    $decimales = ""; // 2 siempre, cualquier numero + relleno en ceros 

    // pico el numero en 2, quizas no se pique por ser un entero
    // $precio = "12.6";
    if($precio == ""){
      // echo("valor vacio de precio\n");
      return false;
    }

    // aqui va la funcion expresion regular validador de numeros
    if (is_numeric($precio) == false){
      // echo("valor invalido cifras\n" + $precio );
      return false;
    }else{
        // echo("valor numerico\n");
    }
    
    return  " ".$Utils->formal_number($precio);

  }


  function translateCantidad($cant = ""){

    $Utils = new Utils();

    //validaciones de cantidad
    // cantidad del ítem (5 enteros + 3 decimales)
    $enteros = "";   // 5 siempre, cualquier numero + relleno en ceros
    $decimales = ""; // 3 siempre, cualquier numero + relleno en ceros 

    // pico el numero en 2, quizas no se pique por ser un entero
    // $cant = "1250.955";
    
    if($cant == ""){
        //echo("valor vacio de cantidad\n");
        return false;
    }

        // aqui va la funcion expresion regular validador de numeros
    if (is_numeric($cant) == false){
        //echo("valor invalido cifras\n" + $cant );
        return false;
    }

      return $cant." x";

  }
    

  function translateDescription($desc = ""){
    
    $max_caracteres = 20; //definido en el manual

    if($desc == ""){
      //echo("Descripcion Vacia\n");
      return false;
    }

    // tengo que reemplazar caracteres que causan errores
    $remplazos_buenos = array("Y","E","N","");
    $caracteres_malvados = array("&","É","Ñ",".");

    $linea_sana = str_replace($caracteres_malvados, $remplazos_buenos, $desc );

    $comando = substr($linea_sana,0,$max_caracteres);

    $comando = preg_replace('/[^A-Za-z0-9Ññ\ ]/','', $comando); //solo acepta caracteres normales

    return  $comando;

  }


  function translateFinalTotal($total){
  
    $Utils = new Utils();

    $total = $Utils->formal_number(floatval($total));

    $total = "Bs ". $total;

    $comando = "80*".$Utils->formal_padding("TOTAL: ",$total,42);
    
    return  $comando;

  }
    

  function translateSubtotal($subtotal){

    $Utils = new Utils();

    $subtotal = $Utils->formal_number(floatval($subtotal));

    $subtotal = "Bs ". $subtotal;

    $comando = "80*".$Utils->formal_padding("SUBTTL: ",$subtotal,42);
    
    return  $comando;

  }
    
  
  function translateTax($tax){
  
    $Utils = new Utils();

    $tax = $Utils->formal_number(floatval($tax));

    $tax = "Bs ". $tax;

    $comando = "80*".$Utils->formal_padding("IVA: ",$tax,42);
    
    return  $comando;

  }
    
  
  function translateTotal($precio = "", $cant = ""){
  
    $Utils = new Utils();

    $comando = "Bs ".$Utils->formal_number(floatval($precio* $cant));  
    
    return  $comando;

  }
    

  function translateLinePrice($tasa="", $precio = "", $cant = "", $desc = ""){
  
    $comando = "80*". $this->translateCantidad($cant)."Bs".$this->translatePrecio($precio) ;
    
    return  $comando;

  }


  function translateLineDesc($tasa="", $precio = "", $cant = "", $desc = ""){
  
    $Utils = new Utils();

    $titulo = $this->translateDescription($desc). $this->translateTasa($tasa);

    $numero = $this->translateTotal($precio, $cant);

    $comando = "80*".$Utils->formal_padding($titulo, $numero, 42);

    return  $comando;

  }


  function translateLine($tasa="", $precio = "", $cant = "", $desc = ""){
  
    $Utils = new Utils();
    
    $titulo = $this->translateDescription($desc). $this->translateTasa($tasa);

    $numero = "Bs".$this->translatePrecio($precio);

    $comando = "80*".$Utils->formal_padding($titulo, $numero, 42);

    return  $comando;

  }
  

  function translateLineCredito($tasa="", $precio = "", $cant = "", $desc = ""){
  
    $Utils = new Utils();
    
    $titulo = $this->translateDescription($desc). $this->translateTasa($tasa);

    $numero = "Bs".$this->translatePrecio($precio);

    $comando = "80*".$Utils->formal_padding($titulo, $numero, 42);

    return  $comando;

  }


  function separador(){
  
    // pendiente del maximo caracteres permitidos por maquina, deber estar adaptado a la config
    $comando = substr("80*"."------------------------------------------------------------------------",0,45)."\n";
    
    return  $comando;

  }
  

  function translateFiscalInfoArray( $InfoFiscal = []){
    // MODELO IMPRESORA  SRP-812
    // ENCABEZADOS X (Y) : 40 (8 líneas)
    // PIE DE PÁGINA X (Y): 40 (8 líneas)         
    // RIF/C.I X: 40
    // RAZÓN SOCIAL X: 40
    // INFORMACIÓN ADICIONAL X (Y):40 (10 líneas)
    // COMENTARIO X:40
    // DESCRIPCIÓN PRODUCTO X:  127

    // ... ejemplo
    // -5 => "iF*0000001\n",//factura asociadaj
    // -4 => "iI*Z4A1234567\n",// numero de control de esa factura
    // -3 => "iD*18-01-2014\n",//fecha factura dia especifico
    // -2 => "iS*Pedro Mendez\n", // mombre persona
    // -1 => "iR*12.345.678\n", // rif

    // ["invoice_number"]=>string(3) "112"
    // ["tax_id"]=> string(1) "1"
    // ["exchange_rate"]=> string(1) "2"
    // ["createdAt"]=> string(19) "22-07-2020"
    // ["name"]=> string(12) "VENMATEX S A"
    // ["last_name"]=> NULL
    // ["telephone"]=> string(11) "02122427233"
    // ["identification_number"]=> string(9) "002985321"
    // ["identification_type_id"]=> string(1) "2"
    // ["direction"]=> string(17) "LA URBINA CARACAS"
    // ["identification_type_name"]=> string(1) "J"
    // ["user_name"]=> string(10) "SUPERVISOR"
    // ["user_lastname"]=> string(10) "SUPERVISOR"
    // ["rol_id"]=> string(1) "2"
    // ["complete_identification"]=> string(xx) "J002985321"
    $contador_inverso = -10; // aqui tengo que poner la cantidad de items que me llegan en reversa. eran 11 con las 2 lineas de comentario fiscal comentadas
    $InfoFiscalTraducida = [];
    $ComentarioTraducido = [];
    $max_caracteres = 40; //definido en el manual
    $max_caracteres_info_adicional = 40; //manual again
    $max_lineas_info_adicional = 10; //manual again
    $max_caracteres_comentario = 40;

    $Utils = new Utils();
    $contador = 0;
    echo("dentro del interprete \n");
    var_dump($InfoFiscal);

    // TITULO
    // -10 => titulo
    $InfoFiscalTraducida[$contador] = "80*"."NOTA DE ENTREGA "."\n";
    $contador++;
    // -9 $InfoFiscalTraducida[$contador_inverso] = "iF*".$InfoFiscal["invoice_number"];
    // $InfoFiscalTraducida[$contador] = "80*"."#FAC: ".$InfoFiscal["invoice_number"]."\n";
    $InfoFiscalTraducida[$contador] = "80*"."#PED: ".$InfoFiscal["saleorder_number"]."\n";
    $contador++;
    // -8 => "iD*18-01-2014\n",//fecha factura dia especifico
    $InfoFiscalTraducida[$contador] = "80*"."FECHA FAC: ".$InfoFiscal["createdAt"]."\n";
    $contador++;
    // -7 => "iS*Pedro Mendez\n", // mombre persona
    $InfoFiscalTraducida[$contador] =  substr("80*"."RIF/C.i: ".$InfoFiscal["complete_identification"],0,$max_caracteres)."\n";
    $contador++;
    // -6 => "iR*12.345.678\n", // rif
    $linea_sin_caracteres_especiales = "RAZON SOCIAL: ".$InfoFiscal["name"]." ".$InfoFiscal["last_name"];
    $linea_sin_caracteres_especiales = $Utils->cleanSpecialChars($linea_sin_caracteres_especiales); //solo acepta caracteres normales
    $InfoFiscalTraducida[$contador] =  substr("80*".$linea_sin_caracteres_especiales,0,$max_caracteres)."\n";
    $contador++;
    // -5 => "i00 algo\n", // info adicional cliente (direccion)
    $contado = ($InfoFiscal["credit"] == "1") ? "CREDITO" : "CONTADO" ;
    $InfoFiscalTraducida[$contador] = substr("80*"."TIPO: ".$contado." ",0,$max_caracteres_info_adicional)."\n";
    $contador++;
    // -4 => "i00 algo\n", // info adicional cliente (telefono)
    $linea_sin_caracteres_especiales = "Telf: ".$InfoFiscal["telephone"];
    $linea_sin_caracteres_especiales = $Utils->cleanSpecialChars($linea_sin_caracteres_especiales); //solo acepta caracteres normales
    $InfoFiscalTraducida[$contador] = substr("80*".$linea_sin_caracteres_especiales,0,$max_caracteres_info_adicional)."\n";
    $contador++;
    // -3 => "i00 algo\n", // info adicional cliente (direccion)
    $linea_sin_caracteres_especiales = "DIR: ".$InfoFiscal["direction"];
    $linea_sin_caracteres_especiales = $Utils->cleanSpecialChars($linea_sin_caracteres_especiales); //solo acepta caracteres normales
    $InfoFiscalTraducida[$contador] = substr("80*".$linea_sin_caracteres_especiales,0,$max_caracteres_info_adicional)."\n";
    $contador++;
    // -2 => "i00 algo\n", // info adicional cliente (direccion)
    $linea_sin_caracteres_especiales = "CAJERO: ".$InfoFiscal["user_name"]." ".$InfoFiscal["user_lastname"];
    $linea_sin_caracteres_especiales = $Utils->cleanSpecialChars($linea_sin_caracteres_especiales); //solo acepta caracteres normales
    $InfoFiscalTraducida[$contador] = substr("80*".$linea_sin_caracteres_especiales,0,$max_caracteres_info_adicional)."\n";
    $contador++;
    // -1! => "i00 algo\n", // info adicional cliente (direccion)
    $linea_sin_caracteres_especiales = "REF: 00P".$InfoFiscal["reference_dolar"]. "XLGGW";
    $linea_sin_caracteres_especiales = $Utils->cleanSpecialChars($linea_sin_caracteres_especiales); //solo acepta caracteres normales
    $InfoFiscalTraducida[$contador] = substr("80*".$linea_sin_caracteres_especiales,0,$max_caracteres_info_adicional)."\n";
    $contador++;

    // // -4 => "i00 algo\n", // info adicional cliente
    // $InfoFiscalTraducida[$contador_inverso] = substr("80*"."Primera linea super larga de informacion fiscal",0,$max_caracteres_info_adicional)."\n";;
    // $contador_inverso++;
    // // -3 => "i00 algo\n", // info adicional cliente
    // $InfoFiscalTraducida[$contador_inverso] = substr("80*"."segunda linea de informacion fiscal?",0,$max_caracteres_info_adicional)."\n";
    // $contador_inverso++;

    // $ComentarioTraducido =  $Utils->makeComment($Utils->splitsize($InfoFiscal["observations"]),"80*");

    // $InfoFiscalTraducida = array_merge($InfoFiscalTraducida,$ComentarioTraducido);

    // $contador +  sizeof($ComentarioTraducido);

    // -1 => "i00 algo\n", // cierre de linea
    $InfoFiscalTraducida[$contador] = $this->separador();

    // esta ultima linea me ayuda a no tener que predecir la cantidad de lineas que tengo
    // sino que modifica un arreglo y crea otro con los cambios de indices requeridos
    $InfoFiscalTraducida = $Utils->rearrangeToNegativeArray($InfoFiscalTraducida);

    return  $InfoFiscalTraducida;

  }

  
  function translateFiscalInfoArrayCreditnote( $InfoFiscal = []){
    // MODELO IMPRESORA  SRP-812
    // ENCABEZADOS X (Y) : 40 (8 líneas)
    // PIE DE PÁGINA X (Y): 40 (8 líneas)         
    // RIF/C.I X: 40
    // RAZÓN SOCIAL X: 40
    // INFORMACIÓN ADICIONAL X (Y):40 (10 líneas)
    // COMENTARIO X:40
    // DESCRIPCIÓN PRODUCTO X:  127

    // ... ejemplo
    // -5 => "iF*0000001\n",//factura asociadaj
    // -4 => "iI*Z4A1234567\n",// numero de control de esa factura
    // -3 => "iD*18-01-2014\n",//fecha factura dia especifico
    // -2 => "iS*Pedro Mendez\n", // mombre persona
    // -1 => "iR*12.345.678\n", // rif

    // ["invoice_number"]=>string(3) "112"
    // ["tax_id"]=> string(1) "1"
    // ["exchange_rate"]=> string(1) "2"
    // ["createdAt"]=> string(19) "22-07-2020"
    // ["name"]=> string(12) "VENMATEX S A"
    // ["last_name"]=> NULL
    // ["telephone"]=> string(11) "02122427233"
    // ["identification_number"]=> string(9) "002985321"
    // ["identification_type_id"]=> string(1) "2"
    // ["direction"]=> string(17) "LA URBINA CARACAS"
    // ["identification_type_name"]=> string(1) "J"
    // ["user_name"]=> string(10) "SUPERVISOR"
    // ["user_lastname"]=> string(10) "SUPERVISOR"
    // ["rol_id"]=> string(1) "2"
    // ["complete_identification"]=> string(xx) "J002985321"
    $contador_inverso = -10; // aqui tengo que poner la cantidad de items que me llegan en reversa. eran 11 con las 2 lineas de comentario fiscal comentadas
    $InfoFiscalTraducida = [];
    $ComentarioTraducido = [];
    $max_caracteres = 40; //definido en el manual
    $max_caracteres_info_adicional = 40; //manual again
    $max_lineas_info_adicional = 10; //manual again
    $max_caracteres_comentario = 40;

    $Utils = new Utils();
    $contador = 0;
    echo("dentro del interprete no fiscal\n");
    var_dump($InfoFiscal);

    // TITULO
    // -10 => titulo
    $InfoFiscalTraducida[$contador] = "80*"."NOTA DE CREDITO "."\n";
    $contador++;
    // -9 $InfoFiscalTraducida[$contador_inverso] = "iF*".$InfoFiscal["invoice_number"];
    $InfoFiscalTraducida[$contador] = "80*"."#FAC PED: ".$InfoFiscal["saleorder_number"]."\n";
    $contador++;
    // -9 $InfoFiscalTraducida[$contador_inverso] = "iF*".$InfoFiscal["invoice_number"];
    $InfoFiscalTraducida[$contador] = "80*"."#NOTA CRED: ".$InfoFiscal["creditnote_number"]."\n";
    $contador++;
    // -8 => "iD*18-01-2014\n",//fecha factura dia especifico
    $InfoFiscalTraducida[$contador] = "80*"."FECHA FAC: ".$InfoFiscal["createdAt"]."\n";
    $contador++;
    // -7 => "iS*Pedro Mendez\n", // mombre persona
    $InfoFiscalTraducida[$contador] =  substr("80*"."RIF/C.i: ".$InfoFiscal["complete_identification"],0,$max_caracteres)."\n";
    $contador++;
    // -6 => "iR*12.345.678\n", // rif
    $linea_sin_caracteres_especiales = "RAZON SOCIAL: ".$InfoFiscal["name"]." ".$InfoFiscal["last_name"];
    $linea_sin_caracteres_especiales = $Utils->cleanSpecialChars($linea_sin_caracteres_especiales); //solo acepta caracteres normales
    $InfoFiscalTraducida[$contador] =  substr("80*".$linea_sin_caracteres_especiales,0,$max_caracteres)."\n";
    $contador++;
    // -4 => "i00 algo\n", // info adicional cliente (telefono)
    $linea_sin_caracteres_especiales ="Telf: ".$InfoFiscal["telephone"];
    $linea_sin_caracteres_especiales = $Utils->cleanSpecialChars($linea_sin_caracteres_especiales); //solo acepta caracteres normales
    $InfoFiscalTraducida[$contador] = substr("80*".$linea_sin_caracteres_especiales,0,$max_caracteres_info_adicional)."\n";
    $contador++;
    // -3 => "i00 algo\n", // info adicional cliente (direccion)
    $linea_sin_caracteres_especiales = "DIR: ".$InfoFiscal["direction"];
    $linea_sin_caracteres_especiales = $Utils->cleanSpecialChars($linea_sin_caracteres_especiales); //solo acepta caracteres normales
    $InfoFiscalTraducida[$contador] = substr("80*".$linea_sin_caracteres_especiales,0,$max_caracteres_info_adicional)."\n";
    $contador++;
    // -2 => "i00 algo\n", // info adicional cliente (direccion)
    $linea_sin_caracteres_especiales = "CAJERO: ".$InfoFiscal["user_name"]." ".$InfoFiscal["user_lastname"];
    $linea_sin_caracteres_especiales = $Utils->cleanSpecialChars($linea_sin_caracteres_especiales); //solo acepta caracteres normales
    $InfoFiscalTraducida[$contador] = substr("80*".$linea_sin_caracteres_especiales,0,$max_caracteres_info_adicional)."\n";
    $contador++;

    // $ComentarioTraducido =  $Utils->makeComment($Utils->splitsize($InfoFiscal["observations"]),"80*");

    // $InfoFiscalTraducida = array_merge($InfoFiscalTraducida,$ComentarioTraducido);

    // $contador +  sizeof($ComentarioTraducido);

    // -1 => "i00 algo\n", // cierre de linea
    $InfoFiscalTraducida[$contador] = $this->separador();

    // esta ultima linea me ayuda a no tener que predecir la cantidad de lineas que tengo
    // sino que modifica un arreglo y crea otro con los cambios de indices requeridos
    $InfoFiscalTraducida = $Utils->rearrangeToNegativeArray($InfoFiscalTraducida);

    return  $InfoFiscalTraducida;

  }

}
?>