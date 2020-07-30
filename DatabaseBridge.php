<?php

  include_once ("TfhkaPHP.php"); 
  include_once ("interpreter.php"); 
  include_once ("Utils.php"); 

  $itObj = new Tfhka(); // printer api

class DatabaseBridge
{
  
  function connect($servername, $username, $password, $dbname, $printer_id){
    /*** (C) CONNECT DATABASE ***/
    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
    }

    // cerrando db (FALTA COLOCARLO EN UN MEJOR LUGAR)
    // $conn->close();

    //retorno la conexion
    return $conn;

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


}
?>