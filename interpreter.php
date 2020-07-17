<?php

class interpreter
{

  function translateTasa($tasa=""){
    // de momento tengo entendido 4 tipos de tasa
    // (falta copiar de manuak, pero en ejemplo tengo)// (!), ("), (#), ( )
    
    if($tasa == ""){
        //echo("valor vacio de tasa\n");
        return false;
    }

    switch ($tasa) {
        case "Exento":
        //echo "Exento\n";
        $comando = " ";
        break;
        case "Tasa 1":
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
    
    // buscar expresion regular compatible con la mision de
    // verificar si un numero es valido
    // verificar si es valido sin decimales
    // verificar si es valido con comas
    // verificar si es valido con puntos
    function validador_numerico($value){
        return (preg_match ('~^((?:\+|-)?[0-9]+)$~' ,$value) == 1);
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

    function translatePrecio( $precio = ""){
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
            $enteros = $this->padding_number_format($cifras_separadas[0],8);
            //echo($enteros);
            break;
        case 2:
            // 
        //  echo "numero + decimales\n";
        //  echo("valor entero\n ". $cifras_separadas[0] . "\n");
        //  echo("valor decimal\n ". $cifras_separadas[1] . "\n");
        
            $enteros = $this->padding_number_format($cifras_separadas[0],8);
            $decimales = $this->padding_decimal_format($cifras_separadas[1],2);  

            break;
        default:
            //echo "formato de numero no reconocido\n";
            return false;
        }
    
        return  $enteros.$decimales;
    }
    
    function translateCantidad($cant = ""){
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
            $enteros = $this->padding_number_format($cifras_separadas[0],5);
            //echo($enteros);
            break;
        case 2:
            //  echo "numero + decimales\n";
            //  echo("valor entero\n ". $cifras_separadas[0] . "\n");
            //  echo("valor decimal\n ". $cifras_separadas[1] . "\n");
            
            $enteros = $this->padding_number_format($cifras_separadas[0],5);
            $decimales = $this->padding_decimal_format($cifras_separadas[1],3);  

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
    
    $comando = $this->translateTasa($tasa, $tipo_doc) .$this->translatePrecio($precio) . $this->translateCantidad($cant) .$this->translateDescription($desc);
    
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
  
}
?>