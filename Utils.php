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
  
  function system_status( ){

    // la idea de este es copiar la info reflejada en C:\IntTFHKA\
    // a las carpetas locales del proyecto y asi poder obtener realmente
    // esos valores para usarlos luego 

    echo("\n ----------------------------------------"); 
    echo("\n        Entro a la llamada status"); 
    echo("\n----------------------------------------"); 

    // en esta linea, llamo al driver a que me obtenga el Status S1
    shell_exec('IntTFHKA.exe UploadStatusCmd("S1", "StatusData.txt")');

    // estas rutas debo meterlas en el archivo de config
    // pero de momentos pueden estar especificadas aca
    $ruta='C:\IntTFHKA\Status';
    $destino='C:\xampp\htdocs\Printer-Interpreter\STATUS\Status';
    $archivos= glob($ruta.'*.*');
    
    // por cada archivo que haga match con el nombre.
    // copiar y reemplazar
    foreach ($archivos as $archivo){
      $archivo_copiar= str_replace($ruta, $destino, $archivo);
      copy($archivo, $archivo_copiar);
    }

    // despues de esas llamadas, deberia interpretar y sacarlo a un arreglo
    // debo hacer lo mismo con S2 y S3 que podrian darme informacion
    // util tambien
    
    // aqui hago la lectura
    $filename = "./STATUS/Status.txt";
    $file = fopen( $filename, "r" );
    
    if( $file == false ) {
       echo ( "\nError in opening file" );
       exit();
    }
    
    $filesize = filesize( $filename );
    $filetext = fread( $file, $filesize );
    fclose( $file );
    
    echo ( "\nFile size : $filesize bytes" );
    echo ( "\n<pre> $filetext </pre>\n" );

    // aqui cuadro el interpreter de la linea de status 
    $S1Printer_status = [];
    $S1Printer_status = $this->S1Interpreter($filetext);

    // echo "termino";
    echo("\n----------------------------------------"); 
    echo("\n       Salgo de la llamada status"); 
    echo("\n----------------------------------------\n"); 
    
    // shell_exec('IntTFHKA.exe UploadStatusCmd("S2", "StatusData.txt")');
    return $S1Printer_status;

  }


  function system_others( ){

     // shell_exec('IntTFHKA.exe UploadStatusCmd("S2", "StatusData.txt")');
    return true;

  }

  
  function S1Interpreter($filetext = ""){
    $arreglo = [];

    // TABLA GUIA
    //  desde hasta long  type    description
    //    1     2     2   ASCII   “S1”
    //    3     4     2   ASCII   Número de Cajero asignado
    //    5     5     1   HEX     Separador 0x0A
    //    6     22    17  ASCII   Total de ventas diarias
    //    23    23    1   HEX     Separador 0x0A
    //    24    31    8   ASCII   Número de la última factura
    //    32    32    1   HEX     Separador 0x0A
    //    33    37    5   ASCII   Cantidad de facturas emitidas en el día
    //    38    38    1   HEX     Separador 0x0A
    //    39    46    8   ASCII   Número de la última nota de débito
    //    47    47    1   HEX     Separador 0x0A
    //    48    52    5   ASCII   Cantidad de notas de débito emitidas en el día
    //    53    53    1   HEX     Separador 0x0A
    //    54    61    8   ASCII   Número de la última nota de crédito
    //    62    62    1   HEX     Separador 0x0A
    //    63    67    5   ASCII   Cantidad de notas de crédito emitidas en el día
    //    68    68    1   HEX     Separador 0x0A
    //    69    76    8   ASCII   Número del último documento no fiscal
    //    77    77    1   HEX     Separador 0x0A
    //    78    82    5   ASCII   Cantidad de documentos no fiscales emitidos en el día
    //    83    83    1   HEX     Separador 0x0A
    //    84    87    4   ASCII   Contador de reportes de Memoria Fiscal
    //    88    88    1   HEX     Separador 0x0A
    //    89    92    4   ASCII   Contador de cierres diarios Z
    //    93    93    1   HEX     Separador 0x0A
    //    94    104   11  ASCII RIF
    //    105   105   1   HEX Separador 0x0A
    //    106   115   10  ASCII Número de Registro de la Máquina
    //    116   116   1   HEX Separador 0x0A
    //    117   122   6   ASCII Hora actual de la impresora (HHMMSS)
    //    123   123   1   HEX Separador 0x0A
    //    124   129   6   ASCII Fecha actual de la impresora (DDMMAA)
    //    130   130   1   HEX Separador 0x0A

    echo("\n ----------------------------------------"); 
    echo("\n        Entro al Interpreter"             ); 
    echo("\n            (Valores)"                    ); 
    echo("\n-----------------------------------------\n"); 

    // NOTA:
    // (recuerda los indices en los strings empiezan en cero)

    // Comando:
    //  desde hasta long  type    description
    //    1     2     2   ASCII   “S1”
    echo  "1. Comando Status Nro. (0) \n";
    $base = 0;
    $cant_caracteres = 2;
    $leido = substr($filetext,$base,$cant_caracteres)."\n";
    echo  " $leido \n\n";
    $base = $base + $cant_caracteres;
    array_push($arreglo,$leido);

    //  desde hasta long  type    description
    //    3     4     2   ASCII   Número de Cajero asignado
    echo  "2. Número de Cajero asignado. (1) \n";
    // $base = 2;
    $cant_caracteres = 2;
    $leido = substr($filetext,$base,$cant_caracteres)."\n";
    echo  " $leido \n\n";
    $base = $base + $cant_caracteres;
    array_push($arreglo,$leido);

    //  desde hasta long  type    description
    //    6     22    17  ASCII   Total de ventas diarias
    echo  "3. Total de ventas diarias (2) \n";
    // $base = 5;
    $cant_caracteres = 17;
    $leido = substr($filetext,$base,$cant_caracteres)."\n";
    echo  " $leido \n\n";
    $base = $base + $cant_caracteres;
    array_push($arreglo,$leido);

    //  desde hasta long  type    description
    //    24    31    8   ASCII   Número de la última factura
    echo  "4. Número de la última factura (3)\n";
    // $base = 23;
    $cant_caracteres = 8;
    $leido = substr($filetext,$base,$cant_caracteres)."\n";
    echo  " $leido \n\n";
    $base = $base + $cant_caracteres;
    array_push($arreglo,$leido);
    
    //  desde hasta long  type    description
    //    33    37    5   ASCII   Cantidad de facturas emitidas en el día
    echo  "5. Cantidad de facturas emitidas en el día (4)\n";
    // $base = 32;
    $cant_caracteres = 5;
    $leido = substr($filetext,$base,$cant_caracteres)."\n";
    echo  " $leido \n\n";
    $base = $base + $cant_caracteres;
    array_push($arreglo,$leido);
    
    //  desde hasta long  type    description
    //    39    46    8   ASCII   Número de la última nota de débito
    echo  "6. Número de la última nota de débito (5)\n";
    // $base = 38;
    $cant_caracteres = 8;
    $leido = substr($filetext,$base,$cant_caracteres)."\n";
    echo  " $leido \n\n";
    $base = $base + $cant_caracteres;
    array_push($arreglo,$leido);

    //  desde hasta long  type    description
    //    48    52    5   ASCII   Cantidad de notas de débito emitidas en el día
    echo  "7. Cantidad de notas de débito emitidas en el día (6)\n";
    // $base = 47;
    $cant_caracteres = 5;
    $leido = substr($filetext,$base,$cant_caracteres)."\n";
    echo  " $leido \n\n";
    $base = $base + $cant_caracteres;
    array_push($arreglo,$leido);
    
    //  desde hasta long  type    description
    //    54    61    8   ASCII   Número de la última nota de crédito
    echo  "8. Número de la última nota de crédito (7)\n";
    // $base = 53;
    $cant_caracteres = 8;
    $leido = substr($filetext,$base,$cant_caracteres)."\n";
    echo  " $leido \n\n";
    $base = $base + $cant_caracteres;
    array_push($arreglo,$leido);
    
    //  desde hasta long  type    description
    //    63    67    5   ASCII   Cantidad de notas de crédito emitidas en el día
    echo  "9. Cantidad de notas de crédito emitidas en el día (8) \n";
    // $base = 62;
    $cant_caracteres = 5;
    $leido = substr($filetext,$base,$cant_caracteres)."\n";
    echo  " $leido \n\n";
    $base = $base + $cant_caracteres;
    array_push($arreglo,$leido);

    //  desde hasta long  type    description
    //    69    76    8   ASCII   Número del último documento no fiscal
    echo  "10. Cantidad de notas de crédito emitidas en el día (9)\n";
    // $base = 68;
    $cant_caracteres = 8;
    $leido = substr($filetext,$base,$cant_caracteres)."\n";
    echo  " $leido \n\n";
    $base = $base + $cant_caracteres;
    array_push($arreglo,$leido);

    //  desde hasta long  type    description
    //    78    82    5   ASCII   Cantidad de documentos no fiscales emitidos en el día
    echo  "11. Cantidad de documentos no fiscales emitidos en el día (10)\n";
    // $base = 77;
    $cant_caracteres = 5;
    $leido = substr($filetext,$base,$cant_caracteres)."\n";
    echo  " $leido \n\n";
    $base = $base + $cant_caracteres;
    array_push($arreglo,$leido);

    //  desde hasta long  type    description
    //    84    87    4   ASCII   Contador de reportes de Memoria Fiscal
    echo  "12. Contador de reportes de Memoria Fiscal (11)\n";
    // $base = 83;
    $cant_caracteres = 4;
    $leido = substr($filetext,$base,$cant_caracteres)."\n";
    echo  " $leido \n\n";
    $base = $base + $cant_caracteres;
    array_push($arreglo,$leido);

    //  desde hasta long  type    description
    //    89    92    4   ASCII   Contador de cierres diarios Z
    echo  "13. Contador de cierres diarios Z (12)\n";
    // $base = 88;
    $cant_caracteres = 4;
    $leido = substr($filetext,$base,$cant_caracteres)."\n";
    echo  " $leido \n\n";
    $base = $base + $cant_caracteres;
    array_push($arreglo,$leido);

    //  desde hasta long  type    description
    //    94    104   11  ASCII   RIF
    echo  "14. RIF empresa (13)\n";
    // $base = 93;
    $cant_caracteres = 11;
    $leido = substr($filetext,$base,$cant_caracteres)."\n";
    echo  " $leido \n\n";
    $base = $base + $cant_caracteres;
    array_push($arreglo,$leido);

    //  desde hasta long  type    description
    //    106   115   10  ASCII Número de Registro de la Máquina
    echo  "15. Número de Registro de la Máquina (14)\n";
    // $base = 105;
    $cant_caracteres = 10;
    $leido = substr($filetext,$base,$cant_caracteres)."\n";
    echo  " $leido \n\n";
    $base = $base + $cant_caracteres;
    array_push($arreglo,$leido);

    //  desde hasta long  type    description
    //    117   122   6   ASCII Hora actual de la impresora (HHMMSS)
    echo  "16. ASCII Hora actual de la impresora (HHMMSS) (15)\n";
    // $base = 116;
    $cant_caracteres = 6;
    $leido = substr($filetext,$base,$cant_caracteres)."\n";
    echo  " $leido \n\n";
    $base = $base + $cant_caracteres;
    array_push($arreglo,$leido);

    //  desde hasta long  type    description
    //    124   129   6   ASCII Fecha actual de la impresora (DDMMAA)
    echo  "17. Fecha actual de la impresora (DDMMAA) (16)\n";
    // $base = 123;
    $cant_caracteres = 6;
    $leido = substr($filetext,$base,$cant_caracteres)."\n";
    echo  " $leido \n\n";
    $base = $base + $cant_caracteres;
    array_push($arreglo,$leido);

    echo("\n ----------------------------------------"); 
    echo("\n        Termino Interpreter"             ); 
    echo("\n            (Valores)"                    ); 
    echo("\n-----------------------------------------\n"); 

    // var_dump($arreglo);

    return($arreglo);
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
            // si el string del arreglo en el retorno tiene algo diferente a true se considera apagada o con errores
            return  "true";
      break;
      case "FALSE":
          // significa que la impresora esta apagada
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
              echo("respuesta del estado  impresora, no esta dentro de las respuestas esperadas");
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
                  echo("error del estado de  impresora, no esta dentro de las respuestas esperadas");
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
                echo("error del estado de  impresora, no esta dentro de las respuestas esperadas");
                echo("error: ". $respuesta_fragmentada[5]);
                echo("\n");
                // die("respuesta inesperada al consultar estado, buscar error."); 
            }
          break;
          default: 
            echo("respuesta del estado  impresora, no esta dentro de las respuestas esperadas");
            echo("status: ". $respuesta_fragmentada[3]);
            echo("\n");
            // die("respuesta inesperada al consultar estado, buscar error."); 
        }

      break;
      case "FALSE":
          // signigica que la impresora esta apagada
          if($respuesta_fragmentada[3] == "0" && $respuesta_fragmentada[5] == "137"){
            return  "Impresora Apagada.";
          }else{
            echo("respuesta del estado  impresora, no esta dentro de las respuestas esperadas");
            echo("status: ".$respuesta_fragmentada[3]);
            echo("Error: ".$respuesta_fragmentada[5]);
            echo("\n");
            die("respuesta inesperada al consultar estado, buscar error (cambio API?)"); 
          }
      break;
      default: 
        echo("respuesta del estado  impresora, no esta dentro de las respuestas esperadas");
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


  function splitsize( $texto = "", $size = 36){
    // tengo comentarios demasiado largos, necesitaba dividirlo en lineas

    $arreglo = str_split($texto, $size);

    return($arreglo);
  }

  function cleanSpecialChars( $texto = ""){
    // tengo comentarios demasiado largos, necesitaba dividirlo en lineas

    $comando = preg_replace('/[^A-Za-z0-9Ññ\ ]/','', $texto);

    return($comando);
  }



  function makeComment( $comments = [], $prefix = "i00" ){
  // transformando elementos de un arreglo en lineas de codigo de comentario.
    $arreglo = [];

    for ($i = 0; $i < sizeof($comments); $i++) {
        $arreglo[$i] = $prefix.$comments[$i]. "\n";
    }

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
    // $respuesta_impresora = $respuestas_impresora[array_rand($respuestas_impresora, 1)];

    // interpretar la respuesta de la impresora
    $respuesta_impresora = $this->respuesta_impresora($respuesta_impresora);

    // en caso de que necesites forzar la respuesta positiva
    // de que la impresora acaba de imprimir
    // $respuesta_impresora  = "true";

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

 
  function printFileFalso( $filename=""){

    // puedes validar el query aca
    echo ( "-------********************************------ \n");
    echo ( "-------                                ------ \n");
    echo ( "-------         IMPRIMIENDO            ------ \n");
    echo ( "-------       (solo consola )          ------ \n");
    echo ( "-------   (  sin conexion FISCAL  )    ------ \n");
    echo ( "-------                                ------ \n");
    echo ( "-------                                ------ \n");
    echo ( "-------********************************------ \n");
    
    // puedes validar el query aca
    echo ( "nombre de archivo  \n");
    echo ( $filename );
   
    // interpretar la respuesta de la impresora
    $respuesta_impresora = "true";

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


  function sendCorte(){

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
    // $respuesta_impresora = $itObj->SendCmd("I0X");
    // $respuesta_impresora = $itObj->SendFileCmd("Corte.txt");
    shell_exec("IntTFHKA.exe SendFileCmd(CMD/Corte.txt");

    // ... para probar voy a decir que la impresora dijo algo 
    // $respuestas_impresora = ["true","false"];
    // $respuesta_impresora = $respuestas_impresora[array_rand($respuestas_impresora, 1)];

    // interpretar la respuesta de la impresora
    // $respuesta_impresora = $this->respuesta_impresora($respuesta_impresora);
    $respuesta_impresora = "true";


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


  function sendCierre(){

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
    // $respuesta_impresora = $itObj->SendCmd("I0Z");
    // $respuesta_impresora = $respuesta_impresora = $itObj->SendFileCmd("Cierre.txt");
    shell_exec("IntTFHKA.exe SendFileCmd(CMD/Cierre.txt");

    // ... para probar voy a decir que la impresora dijo algo 
    // $respuestas_impresora = ["true","false"];
    // $respuesta_impresora = $respuestas_impresora[array_rand($respuestas_impresora, 1)];

    // interpretar la respuesta de la impresora
    // $respuesta_impresora = $this->respuesta_impresora($respuesta_impresora);
    $respuesta_impresora = "true";


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

  function sendTest(){

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
    // $respuesta_impresora = $itObj->SendCmd("I0Z");
    // $respuesta_impresora = $respuesta_impresora = $itObj->SendFileCmd("Cierre.txt");
    shell_exec("IntTFHKA.exe SendFileCmd(CMD/Test.txt");

    // ... para probar voy a decir que la impresora dijo algo 
    // $respuestas_impresora = ["true","false"];
    // $respuesta_impresora = $respuestas_impresora[array_rand($respuestas_impresora, 1)];

    // interpretar la respuesta de la impresora
    // $respuesta_impresora = $this->respuesta_impresora($respuesta_impresora);
    $respuesta_impresora = "true";


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