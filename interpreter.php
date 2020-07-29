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
    
    
  function translateLine( $tasa="", $precio = "", $cant = "", $desc = "",$tipo_doc=""){
  
    $comando = $this->translateTasa($tasa) .$this->translatePrecio($precio) . $this->translateCantidad($cant) .$this->translateDescription($desc);
    
    //echo "\n\nComando Final\n"; 
    return  $comando;

  }


  function test(){

    return  "importado con exito";
    
  }

    
  function respuesta_impresora( $respuesta=""){

    if($respuesta == ""){
        echo("respuesta impresora Vacia\n");
        return false;
    }

    // entrada ejemplo
    // $respuesta = "Retorno: 3 Status: 0       Error: 0";

    // la siguiente expresion es para eliminar multiples espaciosy hacerlo 1 solo
    // ... ej entrada: "Retorno: 3 Status: 0       Error: 0";
    // ... ej salida: "Retorno: 3 Status: 0 Error: 0";
    $respuesta = preg_replace('/\s+/',' ',$respuesta);

    echo("Mensaje de la impresora real: \n");
    echo($respuesta."\n");

    // ahora por el caracter de espacio, pico el string para obtener un arreglo ordenado
    // ... ej salida:
    // [0]=>string(8) "Retorno:"
    // [1]=>string(1) "3"
    // [2]=>string(7) "Status:"
    // [3]=>string(1) "0"
    // [4]=>string(6) "Error:"
    // [5]=>string(1) "0"
    $respuesta_fragmentada = explode(" ",$respuesta); 

    // ya que tengo la respuesta en un arreglo. interpreto si tengo errores
    if($respuesta_fragmentada[5] == "0"){ 
      // si el strig del arreglo de error esta en cero se considera que la impresora no tuvo errores
      return  "true";
    }else{
      return  "false";
    }

  }
  

  function check_impresora_encendida( $respuesta=""){

    if($respuesta == ""){
        echo("respuesta respuesta_impresora_encendida Vacia\n");
        return false;
    }

    // entrada ejemplo
    // $respuesta = "Retorno: TRUE   Status: 0       Error: 0";

    // la siguiente expresion es para eliminar multiples espaciosy hacerlo 1 solo
    // ... ej entrada: "Retorno: 3 Status: 0       Error: 0";
    // ... ej salida: "Retorno: 3 Status: 0 Error: 0";
    $respuesta = preg_replace('/\s+/',' ',$respuesta);

    echo("Mensaje de la impresora real: \n");
    echo($respuesta."\n");

    // ahora por el caracter de espacio, pico el string para obtener un arreglo ordenado
    // ... ej salida:
    // [0]=>string(8) "Retorno:"
    // [1]=>string(1) "TRUE"
    // [2]=>string(7) "Status:"
    // [3]=>string(1) "0"
    // [4]=>string(6) "Error:"
    // [5]=>string(1) "0"
    $respuesta_fragmentada = explode(" ",$respuesta); 

      // ya que tengo la respuesta en un arreglo. interpreto si tengo errores
    switch ($respuesta_fragmentada[1]) {
      case "TRUE": 
            // si el strig del arreglo en el retorno tiene algo diferente a true se considera apagada o con errores
            return  "true";
      break;
      case "FALSE":
          // signigica que la impresora esta apagada
          return  "false";
        break;
      default: 
        echo("respuesta del estado encendido de la impresora, no esta dentro de los parametros esperados");
        echo($respuesta_fragmentada[1]);
        echo("\n");
        die("respuesta inesperada, buscar error (cambio API?)"); 
    }

  }


  function check_estado_impresora( $respuesta=""){

    if($respuesta == ""){
        echo("respuesta extraccion de estado Vacia\n");
        return false;
    }
  
    // entrada ejemplo
    // $respuesta = "Retorno: TRUE   Status: 0       Error: 0";

    // la siguiente expresion es para eliminar multiples espaciosy hacerlo 1 solo
    // ... ej entrada: "Retorno: 3 Status: 0       Error: 0";
    // ... ej salida: "Retorno: 3 Status: 0 Error: 0";
    $respuesta = preg_replace('/\s+/',' ',$respuesta);

    echo("Mensaje de la impresora real: \n");
    echo($respuesta."\n");
  
    // ahora por el caracter de espacio, pico el string para obtener un arreglo ordenado
    // ... ej salida:
    // [0]=>string(8) "Retorno:"
    // [1]=>string(1) "TRUE"
    // [2]=>string(7) "Status:"
    // [3]=>string(1) "0"
    // [4]=>string(6) "Error:"
    // [5]=>string(1) "0"
    $respuesta_fragmentada = explode(" ",$respuesta); 

    // ejemplo: 
    // Retorno: TRUE Status: 4 Error: 0         // normal
    // Retorno: TRUE Status: 4 Error: 1         // sin rollo
    // Retorno: TRUE Status: 4 Error: 2         // tapa abierta
    // Retorno: TRUE Status: 4 Error: 96        // memoria fiscal casi llena
    // Retorno: TRUE Status: 3 Error: 100       // error mefisc
    // Retorno: FALSE Status: 0 Error: 137      // apagada pero normal
    // Retorno: TRUE Status: 5 Error: 0         // error de formato de linea

    // ya que tengo la respuesta en un arreglo. interpreto si tengo errores
    switch ($respuesta_fragmentada[1]) {
      case "TRUE": 
        // encendida
        switch ($respuesta_fragmentada[3]) {
          case "3": 
            // modo no fiscal
            if($respuesta_fragmentada[5] == "100"){
              return  "Error Memoria Fiscal.";
            }else{
              echo("respuesta del estado  impresora, no esta dentro de las respuesas esperadas");
              echo("status: ".$respuesta_fragmentada[3]);
              echo("Error: ".$respuesta_fragmentada[5]);
              echo("\n");
              die("respuesta inesperada al consultar estado, buscar error "); 
            }
          break;
          case "4":
            // modo fiscal
              switch ($respuesta_fragmentada[5]) {
                case "0": 
                  // Retorno: TRUE Status: 4 Error: 0         // normal
                  return  "OK";
                break;
                case "1":
                  // Retorno: TRUE Status: 4 Error: 1         // sin rollo
                  return  "Sin Rollo de Papel.";
                break;
                case "2":
                  // Retorno: TRUE Status: 4 Error: 2         // tapa abierta
                  return  "Tapa de la impresora abierta.";
                break;
                case "96":
                  // Retorno: TRUE Status: 4 Error: 96        // memoria fiscal casi llena
                  return  "OK. (Memoria Fiscal Casi LLena)";
                break;
                default: 
                  echo("error del estado de  impresora, no esta dentro de las respuesas esperadas");
                  echo("error: ". $respuesta_fragmentada[5]);
                  echo("\n");
                  die("respuesta inesperada al consultar estado, buscar error."); 
              }
          break;
          case "5":
            // modo fiscal
            switch ($respuesta_fragmentada[5]) {
              case "0": 
                // Retorno: TRUE Status: 5 Error: 0         // error de formato de linea
                return  "Error de formato de linea a imprimir (comando).";
              break;
              default: 
                echo("error del estado de  impresora, no esta dentro de las respuesas esperadas");
                echo("error: ". $respuesta_fragmentada[5]);
                echo("\n");
                die("respuesta inesperada al consultar estado, buscar error."); 
            }
          break;
          default: 
            echo("respuesta del estado  impresora, no esta dentro de las respuesas esperadas");
            echo("status: ". $respuesta_fragmentada[3]);
            echo("\n");
            die("respuesta inesperada al consultar estado, buscar error."); 
        }

      break;
      case "FALSE":
          // signigica que la impresora esta apagada
          if($respuesta_fragmentada[3] == "0" && $respuesta_fragmentada[5] == "137"){
            return  "Impresora Apagada.";
          }else{
            echo("respuesta del estado  impresora, no esta dentro de las respuesas esperadas");
            echo("status: ".$respuesta_fragmentada[3]);
            echo("Error: ".$respuesta_fragmentada[5]);
            echo("\n");
            die("respuesta inesperada al consultar estado, buscar error (cambio API?)"); 
          }
      break;
      default: 
        echo("respuesta del estado  impresora, no esta dentro de las respuesas esperadas");
        echo($respuesta_fragmentada[1]);
        echo("\n");
        die("respuesta inesperada al consultar estado, buscar error (cambio API?)"); 
    }

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
    $contador_inverso = -10; // aqui tengo que poner la cantidad de items que me llegan en reversa.
    $InfoFiscalTraducida = [];
    $max_caracteres = 40; //definido en el manual
    $max_caracteres_info_adicional = 40; //manual again
    $max_lineas_info_adicional = 10; //manual again
    $max_caracteres_comentario = 40;


    echo("dentro del interprete \n");
    var_dump($InfoFiscal);
    // -10 => "iF*0000001\n",//factura asociadaj
    // $InfoFiscalTraducida[$contador_inverso] = "iF*".$InfoFiscal["invoice_number"];
    $InfoFiscalTraducida[$contador_inverso] = "iF*".$InfoFiscal["invoice_number"]."\n";
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
    // -3 => "i00 algo\n", // info adicional cliente
    $InfoFiscalTraducida[$contador_inverso] = substr("i00"."Primera linea super larga de informacion fiscal",0,$max_caracteres_info_adicional)."\n";;
    $contador_inverso++;
    // -2 => "i00 algo\n", // info adicional cliente
    $InfoFiscalTraducida[$contador_inverso] = substr("i00"."segunda linea de informacion fiscal?",0,$max_caracteres_info_adicional)."\n";;
    $contador_inverso++;
    // -1 => "i00 algo\n", // comentario
    // este se puede hacer por articulo, asi que esto esta mal colocado aca
    $InfoFiscalTraducida[$contador_inverso] = substr("@"."comentario super largo que hay qye cortar",0,$max_caracteres_comentario)."\n";;
    $contador_inverso++;
    
    
    return  $InfoFiscalTraducida;

  }

}
?>