<?php

include_once ("TfhkaPHP.php"); 
include_once ("status_interpreter.php"); 

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

    echo("\n----------------------------------------"); 
    echo("\n        Entro a la llamada status       "); 
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

    if($filesize == 0){return "";}

    $filetext = fread( $file, $filesize );
    fclose( $file );
    
    echo ( "\nFile size : $filesize bytes" );
    echo ( "\n<pre> $filetext </pre>\n" );

    // aqui cuadro el interpreter de la linea de status 
    $StatusInterpreter =  new status_interpreter();
    $S1Printer_status = [];
    $S1Printer_status = $StatusInterpreter->S1Interpreter($filetext);

    // echo "termino";
    echo("\n----------------------------------------"); 
    echo("\n       Salgo de la llamada status       "); 
    echo("\n----------------------------------------\n"); 
    
    // shell_exec('IntTFHKA.exe UploadStatusCmd("S2", "StatusData.txt")');
    return $S1Printer_status;

  }


  function system_status_specified( $Specific_status=""){

    // la idea de este es copiar la info reflejada en C:\IntTFHKA\
    // a las carpetas locales del proyecto y asi poder obtener realmente
    // esos valores para usarlos luego 

    if( $Specific_status == ""  ){
      echo "\n Necesitas especificar un valor o no puedo dar un valor";
      return false;
    }

    // DEBO VALIDAR ESTE INPUT

    echo("\n----------------------------------------"); 
    echo("\n        Entro a la llamada status       "); 
    echo("\n----------------------------------------"); 

    // en esta linea, llamo al driver a que me obtenga el Status S1
    shell_exec('IntTFHKA.exe UploadStatusCmd("'.$Specific_status.'", "StatusData.txt")');

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

    if($filesize == 0){return "";}

    $filetext = fread( $file, $filesize );
    fclose( $file );
    
    echo ( "\nFile size : $filesize bytes" );
    echo ( "\n<pre> $filetext </pre>\n" );


    // $Specific_status aqui hago el switch con los interpretes de estados


    // aqui cuadro el interpreter de la linea de status 
    $StatusInterpreter =  new status_interpreter();
    $S1Printer_status = [];
    $S1Printer_status = $StatusInterpreter->S1Interpreter($filetext);

    // echo "termino";
    echo("\n----------------------------------------"); 
    echo("\n       Salgo de la llamada status       "); 
    echo("\n----------------------------------------\n"); 
    
    // shell_exec('IntTFHKA.exe UploadStatusCmd("S2", "StatusData.txt")');
    return $S1Printer_status;

  }

  
  function copy_systems_status_file( ){
    // de momento placeholder para lo que sera la accion de copiar el estatus en 
    // el directorio local del proyecto

    return true;

  }


  function system_others( ){

     // shell_exec('IntTFHKA.exe UploadStatusCmd("S2", "StatusData.txt")');
    return true;

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
        echo("respuesta inesperada, buscar error (cambio API?)"); 
        sleep(3);
        return "false";
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
          case "1": 
            // modo no fiscal
            if($respuesta_fragmentada[5] == "0"){
              // Nota de credito en cero???
              // Retorno: TRUE     Status: 1       Error: 0
              echo ( "-------********************************------ \n");
              echo ( "-------                                ------ \n");
              echo ( "-------         DOCUMENTO !            ------ \n");
              echo ( "-------       VALORES EN CERO          ------ \n");
              echo ( "-------       NOTA DE CREDITO          ------ \n");
              echo ( "-------                                ------ \n");
              echo ( "-------********************************------ \n");
              return  "Montos en el Documento en CEROS!.";

            }else{
              echo("respuesta del estado  impresora, no esta dentro de las respuestas esperadas");
              echo("status: ".$respuesta_fragmentada[3]);
              echo("Error: ".$respuesta_fragmentada[5]);
              echo("\n");
              // die("respuesta inesperada al consultar estado, buscar error "); 
              echo("respuesta inesperada al consultar estado, buscar error "); 
              sleep(3);
              return ("error de memoria fiscal complejo");            }
          break;
          case "3": 
            // modo no fiscal
            if($respuesta_fragmentada[5] == "100"){
              return  "Error Memoria Fiscal.";
            }else{
              echo("respuesta del estado  impresora, no esta dentro de las respuestas esperadas");
              echo("status: ".$respuesta_fragmentada[3]);
              echo("Error: ".$respuesta_fragmentada[5]);
              echo("\n");
              // die("respuesta inesperada al consultar estado, buscar error "); 
              echo("respuesta inesperada al consultar estado, buscar error "); 
              sleep(3);
              return ("error de memoria fiscal complejo");            }
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
                  // die("respuesta inesperada al consultar estado, buscar error."); 
                  echo("respuesta inesperada al consultar estado, buscar error."); 
                  sleep(3);
                  return "error complejo de respuesta inesperada 5";
              }
          break;
          case "5":
            // modo fiscal
            switch ($respuesta_fragmentada[5]) {
              case "0": 
                // Retorno: TRUE Status: 5 Error: 0         // error de formato de linea
                
                // puedes validar el query aca
                echo ( "-------********************************------ \n");
                echo ( "-------                                ------ \n");
                echo ( "-------         CORRECTO!!!            ------ \n");
                echo ( "-------            424                 ------ \n");
                echo ( "-------   (  estoy en la deteccion  )  ------ \n");
                echo ( "-------   (  ahora solo me toca  )     ------ \n");
                echo ( "-------   (  reproducir con  )         ------ \n");
                echo ( "-------   (    consistencia  )         ------ \n");
                echo ( "-------                                ------ \n");
                echo ( "-------                                ------ \n");
                echo ( "-------                                ------ \n");
                echo ( "-------********************************------ \n");

                return  "Error de formato de linea a imprimir (comando).";
              break;
              default: 
                echo("error del estado de  impresora, no esta dentro de las respuestas esperadas");
                echo("error: ". $respuesta_fragmentada[5]);
                echo("\n");
                // echo("respuesta inesperada al consultar estado, buscar error."); 
                echo("respuesta inesperada al consultar estado, buscar error."); 
                sleep(3);
                return "error complejo de respuesta inesperada case true";
            }
          break;
          default: 
            echo("respuesta del estado  impresora, no esta dentro de las respuestas esperadas");
            echo("status: ". $respuesta_fragmentada[3]);
            echo("\n");
            // die("respuesta inesperada al consultar estado, buscar error."); 
            echo("respuesta inesperada al consultar estado, buscar error."); 
            sleep(3);
            return "error complejo de respuesta inesperada case default";
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
            return("respuesta inesperada al consultar estado, buscar error (cambio API?)"); 
            // sleep(3);
          }
      break;

      case "0":
        // modo fiscal
        switch ($respuesta_fragmentada[3]) {
          case "0":
            // no consigo documento a imprimir 
            // Retorno: 0      Status: 0       Error: 153  
            echo ( "-------********************************------ \n");
            echo ( "-------                                ------ \n");
            echo ( "-------         DOCUMENTO !            ------ \n");
            echo ( "-------         A IMPRIMIR             ------ \n");
            echo ( "-------        NO ENCONTRADO           ------ \n");
            echo ( "-------   (  estoy en la deteccion  )  ------ \n");
            echo ( "-------                                ------ \n");
            echo ( "-------********************************------ \n");

            return  "Error de formato de linea a imprimir (comando).";
          break;
          default: 
            echo("error del estado de  impresora, no esta dentro de las respuestas esperadas");
            echo("CASOS DOCUMENTOS CON ERRORES");
            echo("error: ". $respuesta_fragmentada[5]);
            echo("\n");
            // echo("respuesta inesperada al consultar estado, buscar error."); 
            echo("respuesta inesperada al consultar estado, buscar error."); 
            sleep(3);
            return "error complejo de respuesta inesperada case true";
        }
      break;
      case "15":
        // modo fiscal
        switch ($respuesta_fragmentada[3]) {
          case "0":
            // nota de credito impresa sim problemas
            // Retorno: 15     Status: 0       Error: 0
        
            echo ( "-------********************************------ \n");
            echo ( "-------                                ------ \n");
            echo ( "-------         DOCUMENTO !            ------ \n");
            echo ( "-------         A IMPRIMIR             ------ \n");
            echo ( "-------   (  nota de credito?  )       ------ \n");
            echo ( "-------                                ------ \n");
            echo ( "-------********************************------ \n");

            return  "Error de formato de linea a imprimir (comando).";
          break;
          default: 
            echo("error del estado de  impresora, no esta dentro de las respuestas esperadas");
            echo("CASOS DOCUMENTOS CON ERRORES");
            echo("error: ". $respuesta_fragmentada[5]);
            echo("\n");
            // echo("respuesta inesperada al consultar estado, buscar error."); 
            echo("respuesta inesperada al consultar estado, buscar error."); 
            sleep(3);
            return "error complejo de respuesta inesperada case true";
        }
      break;
      case "11":
        // modo fiscal
        switch ($respuesta_fragmentada[3]) {
          case "0":
            // Nota de credito en cero???
            // Retorno: 11     Status: 0       Error: 128
            echo ( "-------********************************------ \n");
            echo ( "-------                                ------ \n");
            echo ( "-------         DOCUMENTO !            ------ \n");
            echo ( "-------       VALORES EN CERO          ------ \n");
            echo ( "-------                                ------ \n");
            echo ( "-------********************************------ \n");

            return  "Error de formato de linea a imprimir (comando).";
          break;
          default: 
            echo("error del estado de  impresora, no esta dentro de las respuestas esperadas");
            echo("CASOS DOCUMENTOS CON ERRORES");
            echo("error: ". $respuesta_fragmentada[5]);
            echo("\n");
            // echo("respuesta inesperada al consultar estado, buscar error."); 
            echo("respuesta inesperada al consultar estado, buscar error."); 
            sleep(3);
            return "error complejo de respuesta inesperada case true";
        }
      break;

      default: 
        echo("respuesta del estado  impresora, no esta dentro de las respuestas esperadas");
        echo($respuesta_fragmentada[1]);
        echo("\n");
        // die("respuesta inesperada al consultar estado, buscar error (cambio API?)"); 
        return("respuesta inesperada al consultar estado, buscar error (cambio API?)"); 
        // sleep(3);
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


  function sendCierreManualDoc()
  {

    // Check connection
    $sentencia = "IntTFHKA.exe SendCmd(199";

    shell_exec($sentencia);
    
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


  function sendReimpresionZs($intervalo=""){
    // ejemplo:
    // IntTFHKA.exe SendCmd(Rz02209190220919

    // Check connection
    $sentencia = "IntTFHKA.exe SendCmd(Rz0".$intervalo;

    shell_exec($sentencia);

    echo($sentencia."\n");
    
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

  function sendResumenZs($intervalo=""){
    // ejemplo:
    // IntTFHKA.exe SendCmd(Rz02209190220919

    // Check connection
    $sentencia = "IntTFHKA.exe SendCmd(I2S".$intervalo;

    shell_exec($sentencia);

    echo($sentencia."\n");
    
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


  function sendAnulacionDocManual()
  {

    // Check connection
    $sentencia = "IntTFHKA.exe SendCmd(7";

    shell_exec($sentencia);
    
    // interpretar la respuesta de la impresora
    $respuesta_impresora = "true";

    if($respuesta_impresora == "true"){
      
      return "true";

    }else{
      // (3) (false)  verifico el mensaje del controlador al imprimir, (condiciones de parseo), si sale un error
      // ... se mantiene la factura en current (sin cambios)
      echo "Le envie a la impresora el comando anular documento... (hay que colocar los errores en log, de ser necesario)\n";
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