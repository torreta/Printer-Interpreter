<?php

include_once ("TfhkaPHP.php"); 

class Utils
{
  
  function validador_numerico($value){
      // buscar expresion regular compatible con la mision de
      // verificar si un numero es valido
      // verificar si es valido sin decimales
      // verificar si es valido con comas
      // verificar si es valido con puntos
      return (preg_match ('~^((?:\+|-)?[0-9]+)$~' ,$value) == 1);
  }
    

  function formal_number($num){

    $english_format_number = number_format($num, 2, '.', ',');
  
    return $english_format_number;
  }


  function formal_padding($texto,$num,$max_chars_line){
  
    $cifras_padding = $max_chars_line - strlen($texto) - strlen($num);
  
    $padding = "";
  
    // construyo cuantos ceros falten para completar el padding 
    for ($i = 1; $i <= $cifras_padding; $i++) {
      $padding = $padding . " ";
    } 
  
    return $texto.$padding.$num;
  }


  function padding_number_format($value="", $max_cifras="" ){

    // echo("valor precio\n" . $value . "\n");
    // echo("valor cifras\n" . $max_cifras . "\n");

    $cifras_padding = $max_cifras - strlen($value) ;
    // echo("padding cifras \n" . $cifras_padding . "\n");
    if( $cifras_padding < 0){echo "numero de cifras permitidas excedido"; }

    $padding ="";

    // construyo cuantos ceros falten para completar el padding 
    for ($i = 1; $i <= $cifras_padding; $i++) {
        $padding = $padding . "0";
    } 

    // echo(" padding y valor: \n" . $padding . $value ." \n");
    return $padding . $value;

  }


  function padding_decimal_format($value="", $max_cifras="" ){

    //  echo("valor precio\n" . $value . "\n");
    //  echo("valor cifras\n" . $max_cifras . "\n");

    $cifras_padding = $max_cifras - strlen($value) ;
    // echo("padding cifras \n" . $cifras_padding . "\n");
    if( $cifras_padding < 0){echo "numero de cifras decimales permitidas excedido"; }

    $padding ="";

    // construyo cuantos ceros falten para completar el padding 
    for ($i = 1; $i <= $cifras_padding; $i++) {
        $padding = $padding . "0";
    } 

    // echo(" padding y valor: \n" . $value . $padding ." \n");
    return  $value. $padding;

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


  function rearrangeToNegativeArray( $arreglo = []){
    // habia problemas arreglando indices 
    // era necesario poder sumar arreglos y
    // que el primer arreglo fuera en orden
    // sin importar que el indice fuera tomado por el 
    // segundo arreglo

    $arreglo_inv = [];

    $arreglo = array_reverse($arreglo);

    for ($i = 0; $i < sizeof($arreglo); $i++) {
      $arreglo_inv[-$i-1] = $arreglo[$i];
    }

    $arreglo =  array_reverse($arreglo_inv,true);

    return($arreglo);
  }


  function splitsize( $texto = "", $size = 40){

    $arreglo = str_split($texto, $size);

    return($arreglo);
  }

  
  function printFileFromArray($ArrayInfo, $filename=""){

    // escribo en un archivo el contenido de un array
    // .. por ejemplo el array de una factura
    $file = $filename;	

      $fp = fopen($file, "w+");
      $write = fputs($fp, "");
                      
    foreach($ArrayInfo as $campo => $cmd)
    {
      $write = fputs($fp, $cmd);
    }
    
    //cierro dicho archivo
    fclose($fp);

    return $file;

  }
  

  function printFile( $filename=""){

   $itObj = new Tfhka();

    // verifico si la impresora esta encendida
    // ... colocar condicionales para proceder (FALTA)
    $impresora_encendida = $itObj->CheckFprinter();
    $impresora_encendida = $this->check_impresora_encendida($impresora_encendida);
                  
    // puedes validar el query aca
    echo ( "impresora encendida \n");
    echo ( $impresora_encendida );
    echo ( "\n");


    // verifico estados de la impresora
    $respuesta_impresora_estado = $itObj->ReadFpStatus();
    $respuesta_impresora_estado = $this->check_estado_impresora($respuesta_impresora_estado);

                  
    // puedes validar el query aca
    echo ( "impresora estado de impresora aparte \n");
    echo ( $respuesta_impresora_estado );
    echo ( "\n");

    // enviarlo a imprimir (PROBARLO Y EJECUTARLO IMPRESORA)(FALTA)
    // ... enviar a imprimir
    $respuesta_impresora = $itObj->SendFileCmd($filename);

    // ... para probar voy a decir que la impresora dijo algo 
    // $respuestas_impresora = ["true","false"];
    //$respuesta_impresora = $respuestas_impresora[array_rand($respuestas_impresora, 1)];

    // interpretar la respuesta de la impresora
    $respuesta_impresora = $this->respuesta_impresora($respuesta_impresora);

    if($respuesta_impresora == "true"){
      
      return "true";

    }else{
      // (3) (false)  verifico el mensaje del controlador al imprimir, (condiciones de parseo), si sale un error
      // ... se mantiene la factura en current (sin cambios)
      echo "la impresora fallo... (hay que colocar los errores en log)\n";
      // ... busco en checkprinter cual puede ser la razon del error.
      return "false";
    }


  }



}
?>