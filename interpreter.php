<?php

  include_once ("Utils.php"); 

class interpreter{

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
      $comando = " ";
      break;
      case "IVA 16%":
      //echo "Tasa 1\n";
      $comando = "!";
      break;
      case "Tasa 2":
      //echo "Tasa 2\n";
      $comando = "\"";
      break;
      case "tasa 3":
      //echo "Tasa 3\n";
      $comando = "#";
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


  function translateTasaCredito($tasa=""){
    // de momento tengo entendido 4 tipos de tasa
    // (falta copiar de manuak, pero en ejemplo tengo)// (!), ("), (#), ( )
    
    if($tasa == ""){
        echo("valor vacio de tasa \n"); die;
        return false;
    }

    switch ($tasa) {
      case "Sin IVA":
      //echo "Exento\n";
      $comando = "d0";
      break;
      case "IVA 16%":
      //echo "Tasa 1\n";
      $comando = "d1";
      break;
      case "Tasa 2":
      //echo "Tasa 2\n";
      $comando = "d2";
      break;
      case "tasa 3":
      //echo "Tasa 3\n";
      $comando = "d3";
      break;
      default:
      //echo "Tasa no reconocida\n";
      $comando = false;
      $tasas = ["d0","d1","d2","d3"];
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

    // se hace esto porque la cifra y los decimales en la traduccion no tienen
    // ningun tipo de marcacion, solo se asume que son los ultimos 2 digitos los decimales
    
    // separo la cifra en 2 pedazos, entero y decimal para poder evaluarlo aparte
    $cifras_separadas = explode(".",$precio); 

    // evaluo en la cantidad de pedazos en que se pico el numero, si es anormal se descarta
    $cant_cifras = count($cifras_separadas);
    
    //echo("cant cifras \n" . $cant_cifras."\n");

    // con solo parte entera tengo que agregar padding decimal
    // y tengo que completar lo que sea el numero entero a 8 digitos con padding
    // de ceros.

    switch ($cant_cifras) {
      case 1:
        // con solo parte entera tengo que agregar padding decimal
        //echo "solo numero sin decimales\n";
        //echo("valor entero\n ". $cifras_separadas[0] . "\n");
        $decimales = "00";
        $enteros = $Utils->padding_number_format($cifras_separadas[0],8);
        //echo($enteros);
        break;
      case 2:
        // 
        //  echo "numero + decimales\n";
        //  echo("valor entero\n ". $cifras_separadas[0] . "\n");
        //  echo("valor decimal\n ". $cifras_separadas[1] . "\n");
        
        $enteros = $Utils->padding_number_format($cifras_separadas[0],8);
        $decimales = $Utils->padding_decimal_format($cifras_separadas[1],2);  

        break;
      default:
        //echo "formato de numero no reconocido\n";
        return false;

    }
    
      return  $enteros.$decimales;

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

    // se hace esto porque la cifra y los decimales en la traduccion no tienen
    // ningun tipo de marcacion, solo se asume que son los ultimos 3 digitos son decimales
    
    // separo la cifra en 2 pedazos, entero y decimal para poder evaluarlo aparte
    $cifras_separadas = explode(".",$cant); 

    // evaluo en la cantidad de pedazos en que se pico el numero, si es anormal se descarta
    $cant_cifras = count($cifras_separadas);
    
    //echo("cant cifras \n" . $cant_cifras."\n");

    // con solo parte entera tengo que agregar padding decimal
    // y tengo que completar lo que sea el numero entero a 5 digitos con padding
    // de ceros.

    switch ($cant_cifras) {
      case 1:
        // con solo parte entera tengo que agregar padding decimal
        //  echo "solo numero sin decimales\n";
        //  echo("valor entero\n ". $cifras_separadas[0] . "\n");
        $decimales = "000";
        $enteros = $Utils->padding_number_format($cifras_separadas[0],5);
        //echo($enteros);
        break;
      case 2:
        //  echo "numero + decimales\n";
        //  echo("valor entero\n ". $cifras_separadas[0] . "\n");
        //  echo("valor decimal\n ". $cifras_separadas[1] . "\n");
        
        $enteros = $Utils->padding_number_format($cifras_separadas[0],5);
        $decimales = $Utils->padding_decimal_format($cifras_separadas[1],3);  

        break;
      default:
        //echo "formato de numero no reconocido cantidad\n";
        return false;
    }
    
      return  $enteros.$decimales;

  }
    

  function translateDescription($desc = ""){
    
    $max_caracteres = 20; //definido en el manual

    if($desc == ""){
      //echo("Descripcion Vacia\n");
      return false;
    }

    $comando = substr($desc,0,$max_caracteres);

    return  $comando;

  }
    
    
  function translateLine( $tasa="", $precio = "", $cant = "", $desc = ""){
  
    $comando = $this->translateTasa($tasa) .$this->translatePrecio($precio) . $this->translateCantidad($cant) .$this->translateDescription($desc);
    
    //echo "\n\nComando Final\n"; 
    return  $comando;

  }


  function translateLineCredito( $tasa="", $precio = "", $cant = "", $desc = ""){
  
    $comando = $this->translateTasaCredito($tasa) .$this->translatePrecio($precio) . $this->translateCantidad($cant) .$this->translateDescription($desc);
    
    //echo "\n\nComando Final\n"; 
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
    $contador_inverso = -8; // aqui tengo que poner la cantidad de items que me llegan en reversa. era 10 con las 3 lineas de abajo comentadas.
    $InfoFiscalTraducida = [];
    $max_caracteres = 40; //definido en el manual
    $max_caracteres_info_adicional = 40; //manual again
    $max_lineas_info_adicional = 10; //manual again
    $max_caracteres_comentario = 40;


    echo("dentro del interprete \n");
    var_dump($InfoFiscal);
    // -8 => "iF*0000001\n",//factura asociadaj
    // $InfoFiscalTraducida[$contador_inverso] = "iF*".$InfoFiscal["invoice_number"];
    $InfoFiscalTraducida[$contador_inverso] = "iF*".$InfoFiscal["invoice_number"]."\n";
    $contador_inverso++;
    // -7 => "iD*18-01-2014\n",//fecha factura dia especifico
    $InfoFiscalTraducida[$contador_inverso] = "iD*".$InfoFiscal["createdAt"]."\n";
    $contador_inverso++;
    // -6 => "iS*Pedro Mendez\n", // mombre persona
    $InfoFiscalTraducida[$contador_inverso] =  substr("iS*".$InfoFiscal["name"].$InfoFiscal["last_name"],0,$max_caracteres)."\n";
    $contador_inverso++;
    // -5 => "iR*12.345.678\n", // rif
    $InfoFiscalTraducida[$contador_inverso] = "iR*".$InfoFiscal["complete_identification"]."\n";
    $contador_inverso++;
    // -4 => "i00 algo\n", // info adicional cliente (direccion)
    $contado = ($InfoFiscal["credit"] == "1") ? "CREDITO" : "CONTADO" ;
    $InfoFiscalTraducida[$contador_inverso] = substr("i00"."TIPO: ".$contado." ",0,$max_caracteres_info_adicional)."\n";
    $contador_inverso++;
    // -3 => "i00 algo\n", // info adicional cliente (telefono)
    $InfoFiscalTraducida[$contador_inverso] = substr("i00"."Telf: ".$InfoFiscal["telephone"],0,$max_caracteres_info_adicional)."\n";
    $contador_inverso++;
    // -2 => "i00 algo\n", // info adicional cliente (direccion)
    $InfoFiscalTraducida[$contador_inverso] = substr("i00"."DIR: ".$InfoFiscal["direction"],0,$max_caracteres_info_adicional)."\n";
    $contador_inverso++;
    // -1 => "i00 algo\n", // info adicional cliente (direccion)
    $InfoFiscalTraducida[$contador_inverso] = substr("i00"."CAJERO: ".$InfoFiscal["user_name"]." ".$InfoFiscal["user_lastname"],0,$max_caracteres_info_adicional)."\n";
    $contador_inverso++;
    // // -3 => "i00 algo\n", // info adicional cliente
    // $InfoFiscalTraducida[$contador_inverso] = substr("i00"."Primera linea super larga de informacion fiscal",0,$max_caracteres_info_adicional)."\n";;
    // $contador_inverso++;
    // // -2 => "i00 algo\n", // info adicional cliente
    // $InfoFiscalTraducida[$contador_inverso] = substr("i00"."segunda linea de informacion fiscal?",0,$max_caracteres_info_adicional)."\n";;
    // $contador_inverso++;
    // // -1 => "i00 algo\n", // comentario
    // // este se puede hacer por articulo, asi que esto esta mal colocado aca
    // $InfoFiscalTraducida[$contador_inverso] = substr("@"."comentario super largo que hay qye cortar",0,$max_caracteres_comentario)."\n";;
    // $contador_inverso++;
    
    
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

    $contador_inverso = -8; // aqui tengo que poner la cantidad de items que me llegan en reversa. era 10 con las 3 lineas de abajo comentadas.
    $InfoFiscalTraducida = [];
    $max_caracteres = 40; //definido en el manual
    $max_caracteres_info_adicional = 40; //manual again
    $max_lineas_info_adicional = 10; //manual again
    $max_caracteres_comentario = 40;

    echo("dentro del interprete \n");
    var_dump($InfoFiscal);
    // -10 => "iF*0000001\n",//factura asociadaj
    // $InfoFiscalTraducida[$contador_inverso] = "iF*".$InfoFiscal["invoice_number"];
    // $InfoFiscalTraducida[$contador_inverso] = "iF*".$InfoFiscal["creditnote_number"]."\n";
    // $contador_inverso++;
    //numero doc
    $InfoFiscalTraducida[$contador_inverso] = "iF*".$InfoFiscal["creditnote_number"]."\n";
    $contador_inverso++;
    // -9 => "iD*18-01-2014\n",//fecha factura dia especifico
    $InfoFiscalTraducida[$contador_inverso] = "iD*".$InfoFiscal["createdAt"]."\n";
    $contador_inverso++;
    // -8 => "iS*Pedro Mendez\n", // mombre persona
    $InfoFiscalTraducida[$contador_inverso] =  substr("iS*".$InfoFiscal["name"].$InfoFiscal["last_name"],0,$max_caracteres)."\n";
    $contador_inverso++;
    // -7 => "iR*12.345.678\n", // rif
    $InfoFiscalTraducida[$contador_inverso] = "iR*".$InfoFiscal["complete_identification"]."\n";
    $contador_inverso++;
    // -6 => "i00 algo\n", // info adicional cliente (telefono)
    $InfoFiscalTraducida[$contador_inverso] = substr("i00"."Telf: ".$InfoFiscal["telephone"],0,$max_caracteres_info_adicional)."\n";
    $contador_inverso++;
    // -5 => "i00 algo\n", // info adicional cliente (direccion)
    $InfoFiscalTraducida[$contador_inverso] = substr("i00"."DIR: ".$InfoFiscal["direction"],0,$max_caracteres_info_adicional)."\n";
    $contador_inverso++;
    // -4 => "i00 algo\n", // info adicional cliente (direccion)
    $InfoFiscalTraducida[$contador_inverso] = substr("i00"."CAJERO: ".$InfoFiscal["user_name"]." ".$InfoFiscal["user_lastname"],0,$max_caracteres_info_adicional)."\n";
    $contador_inverso++;
    // // -3 => "i00 algo\n", // info adicional cliente
    $InfoFiscalTraducida[$contador_inverso] = substr("i00".$InfoFiscal["observations"],0,$max_caracteres_info_adicional)."\n";;
    $contador_inverso++;
    // // -2 => "i00 algo\n", // info adicional cliente
    // $InfoFiscalTraducida[$contador_inverso] = substr("i00"."segunda linea de informacion fiscal?",0,$max_caracteres_info_adicional)."\n";;
    // $contador_inverso++;
    // // -1 => "i00 algo\n", // comentario
    // // este se puede hacer por articulo, asi que esto esta mal colocado aca
    // $InfoFiscalTraducida[$contador_inverso] = substr("@"."comentario super largo que hay qye cortar",0,$max_caracteres_comentario)."\n";;
    // $contador_inverso++;
    
    
    return  $InfoFiscalTraducida;

  }


}
?>