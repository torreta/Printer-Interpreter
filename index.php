<HTML>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=iso-8859-1">
<head>
<!-- <title>Demo PHP IntTFHKA</title> -->
<title>Experimento de momento para strix</title>
</head>
<BODY>
<div align = "center"><br>
<B>DEMO FISCAL PHP Windows</B><br><br>
	
	<form id="form1" name="form1" method="post" action = "index.php">
            Configurar Puerto Serial:
		
			<select name="PortName">
				<option value="COM1">COM1
				<option value="COM2">COM2
				<option value="COM3">COM3
				<option value="COM4">COM4
				<option value="COM5">COM5
				<option value="COM9">COM9
			</select>
				
		
            <input name ="EnviarComando" type = "submit"  value="SetPort" /></br></br>
Comando: <input type = "text" name = "Comando" />
<input name ="EnviarComando" type = "submit" value = "Enviar" /></br></br>
<input name ="EnviarComando" type = "submit"  value="SubirS1" />
<input name ="EnviarComando" type = "submit"  value="SubirS2" />
<input name ="EnviarComando" type = "submit"  value="SubirS3"  />
<input name ="EnviarComando" type = "submit"  value="SubirS4" />
<input name ="EnviarComando" type = "submit"  value="SubirS5" />
<input name ="EnviarComando" type = "submit"  value="SubirS6"  />
<input name ="EnviarComando" type = "submit"  value="SubirU0X" />
<input name ="EnviarComando" type = "submit"  value="SubirU0Z" />
</br>
</br>
<input name ="EnviarComando" type = "submit"  value="Facturar" />
<input name ="EnviarComando" type = "submit"  value="Devolucion" />
</br>
</br>
<input name ="EnviarComando" type = "submit"  value="ReporteX" />
<input name ="EnviarComando" type = "submit"  value="ReporteZ" />
</br>
</br>
<input name ="EnviarComando" type = "submit"  value="fpstatus" />
</form>
</div>
<?php
	include_once ("TfhkaPHP.php"); 

    $Foperacion = null;
    
    if(isset($_POST["EnviarComando"]))
    { $Foperacion = $_POST["EnviarComando"]; }

    $itObj = new Tfhka();

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
           $enteros = padding_number_format($cifras_separadas[0],8);
           //echo($enteros);
           break;
         case 2:
           // 
          //  echo "numero + decimales\n";
          //  echo("valor entero\n ". $cifras_separadas[0] . "\n");
          //  echo("valor decimal\n ". $cifras_separadas[1] . "\n");
       
           $enteros = padding_number_format($cifras_separadas[0],8);
           $decimales = padding_decimal_format($cifras_separadas[1],2);  
 
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
           $enteros = padding_number_format($cifras_separadas[0],5);
           //echo($enteros);
           break;
         case 2:
           // 
          //  echo "numero + decimales\n";
          //  echo("valor entero\n ". $cifras_separadas[0] . "\n");
          //  echo("valor decimal\n ". $cifras_separadas[1] . "\n");
       
           $enteros = padding_number_format($cifras_separadas[0],5);
           $decimales = padding_decimal_format($cifras_separadas[1],3);  
 
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
     
     $comando = translateTasa($tasa, $tipo_doc) .translatePrecio($precio) . translateCantidad($cant) .translateDescription($desc);
     
      //echo "\n\nComando Final\n"; 
     
     return  $comando;
    }
 
if (isset($Foperacion)){
  $out = "";
   if ($Foperacion == "Enviar") {
                $out =  $itObj->SendCmd($_POST["Comando"]);
	  }elseif ($Foperacion == "SubirS1") {
			$out =  $itObj->UploadStatusCmd("S1", "StatusData.txt");
	  }elseif ($Foperacion == "SubirS2") {
			$out =  $itObj->UploadStatusCmd("S2", "StatusData.txt");
	  }elseif ($Foperacion == "SubirS3") {
			$out =  $itObj->UploadStatusCmd("S3", "StatusData.txt");
	  }elseif ($Foperacion == "SubirS4") {
			$out =  $itObj->UploadStatusCmd("S4", "StatusData.txt");
	  }elseif ($Foperacion == "SubirS5") {
			$out =  $itObj->UploadStatusCmd("S5", "StatusData.txt");
	  }elseif ($Foperacion == "SubirS6") {
			$out =  $itObj->UploadStatusCmd("S6", "StatusData.txt");
	  }elseif ($Foperacion == "SubirU0X") {
			$out =  $itObj->UploadReportCmd("U0X" , "ReportData.txt");
	  }elseif ($Foperacion == "SubirU0Z") {
      $out =  $itObj->UploadReportCmd("U0Z" , "ReportData.txt");
    }elseif ($Foperacion == "fpstatus") {
      var_dump("entro aca");
			$out =  $itObj->ReadFpStatus();
	  }elseif ($Foperacion == "Facturar") {

      $servername = "localhost";
      $username = "root";
      $password = null;
      $dbname = "pos_development";
      
      // Create connection
      $conn = new mysqli($servername, $username, $password, $dbname);
      
      // Check connection
      if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
      }

      echo "Connected successfully \n";

      $sql = "
        SELECT
          dbo_administration_invoices_items.id,
          dbo_administration_invoices_items.price,
          dbo_administration_invoices_items.quantity, 
          dbo_administration_invoices_items.tax_id,
          dbo_config_taxes.percentage,
          dbo_config_taxes.observation,
          dbo_administration_invoices_items.exchange_rate_id,
          dbo_config_exchange_rates.exchange_rate,
          dbo_config_currencies.abbreviation,
          dbo_config_currencies.`name`,
          dbo_storage_products.`code`,
          dbo_storage_products.description
        FROM `dbo_administration_invoices_items`
        join dbo_config_taxes on dbo_administration_invoices_items.tax_id = dbo_config_taxes.id
        join dbo_config_exchange_rates on dbo_administration_invoices_items.exchange_rate_id = dbo_config_exchange_rates.id
        join dbo_config_currencies on dbo_config_exchange_rates.currency_id = dbo_config_currencies.id
        join dbo_storage_products on dbo_administration_invoices_items.product_id = dbo_storage_products.id;
      ";

      $result = $conn->query($sql);
     
      $factura = array();
      $index_counter = 0;

      if ($result->num_rows > 0) {
        // output data of each row
        while($row = $result->fetch_assoc()) {
          echo "price: " . $row["price"]. " - quantity: " . $row["quantity"]. ", description " . $row["description"]. "<br>";
          echo "\n";

          // $factura[$index_counter] = "price: " . $row["price"]. " - quantity: " . $row["quantity"]. ", description " . $row["description"]. "<br> \n";
          $factura[$index_counter] = translateLine("X",$row["price"],$row["quantity"],$row["description"])."\n";
          $index_counter++;
        }

        //cierre de factura
        $factura[$index_counter] = "101";
 
      } else {
        echo "0 results";
      }

      //cerrando db
      $conn->close();

      // var_dump( $factura); 

      $factura = array(
        -5 => "iF*0000001\n",//factura asociadaj
        -4 => "iI*Z4A1234567\n",// numero de control de esa factura
        -3 => "iD*18-01-2014\n",//fecha factura dia especifico
        -2 => "iS*Pedro Mendez\n", // mombre persona
        -1 => "iR*12.345.678\n", // rif
         0 => "!000001000000001000Harina\n",
         1 => "!000001000000001000Jamon\n",
         2 => " 000001000000001000caracteres especiale\n",
         3 => "#000001000000001000Caja de Whisky\n",
         4 => "#000001000000001000Caja de Chocolates\n",
         5 => "!000001000000001000Maracas de Peltre\n",
         6 => "\"000001000000001000Maracas de Aluminio\n",
         7 => " 000001000000001000Maracas Pesadas\n",
         8 => "#000001000000001000Caja de vainilla\n",
         9 => "!000001000000001000Maracas de goma\n",

        //  10 => "!100000580910000512Harina\n",
        //  11 => "!000000150000001500Jamon\n",
        //  12 => " 050540960300582661caracteres especiale\n",
        //  13 => "#000005000000001000Caja de Whisky\n",
        //  14 => "#000005000000001000Caja de Chocolates\n",
        //  15 => "!000001000000004000Maracas de Peltre\n",
        //  16 => "\"000001000000004000Maracas de Aluminio\n",
        //  17 => " 000001000000004000Maracas Pesadas\n",
        //  18 => "#000005000000001000Caja de vainilla\n",
        //  19 => "!000001000000004000Maracas de goma\n",
         
        //  20 => "!100000580910000512Harina\n",
        //  21 => "!000000150000001500Jamon\n",
        //  22 => " 050540960300582661caracteres especiale\n",
        //  23 => "#000005000000001000Caja de Whisky\n",
        //  24 => "#000005000000001000Caja de Chocolates\n",
        //  25 => "!000001000000004000Maracas de Peltre\n",
        //  26 => "\"000001000000004000Maracas de Aluminio\n",
        //  27 => " 000001000000004000Maracas Pesadas\n",
        //  28 => "#000005000000001000Caja de vainilla\n",
        //  29 => "!000001000000004000Maracas de goma\n",

        //  30 => "!100000580910000512Harina\n",
        //  31 => "!000000150000001500Jamon\n",
        //  32 => " 050540960300582661caracteres especiale\n",
        //  33 => "#000005000000001000Caja de Whisky\n",
        //  34 => "#000005000000001000Caja de Chocolates\n",
        //  35 => "!000001000000004000Maracas de Peltre\n",
        //  36 => "\"000001000000004000Maracas de Aluminio\n",
        //  37 => " 000001000000004000Maracas Pesadas\n",
        //  38 => "#000005000000001000Caja de vainilla\n",
        //  39 => "!000001000000004000Maracas de goma\n",

        //  40 => "!100000580910000512Harina\n",
        //  41 => "!000000150000001500Jamon\n",
        //  42 => " 050540960300582661caracteres especiale\n",
        //  43 => "#000005000000001000Caja de Whisky\n",
        //  44 => "#000005000000001000Caja de Chocolates\n",
        //  45 => "!000001000000004000Maracas de Peltre\n",
        //  46 => "\"000001000000004000Maracas de Aluminio\n",
        //  47 => " 000001000000004000Maracas Pesadas\n",
        //  48 => "#000005000000001000Caja de vainilla\n",
        //  49 => "!000001000000004000Maracas de goma\n",

        //  50 => "!100000580910000512Harina\n",
        //  51 => "!000000150000001500Jamon\n",
        //  52 => " 050540960300582661caracteres especiale\n",
        //  53 => "#000005000000001000Caja de Whisky\n",
        //  54 => "#000005000000001000Caja de Chocolates\n",
        //  55 => "!000001000000004000Maracas de Peltre\n",
        //  56 => "\"000001000000004000Maracas de Aluminio\n",
        //  57 => " 000001000000004000Maracas Pesadas\n",
        //  58 => "#000005000000001000Caja de vainilla\n",
        //  59 => "!000001000000004000Maracas de goma\n",

        //  60 => "!100000580910000512Harina\n",
        //  61 => "!000000150000001500Jamon\n",
        //  62 => " 050540960300582661caracteres especiale\n",
        //  63 => "#000005000000001000Caja de Whisky\n",
        //  64 => "#000005000000001000Caja de Chocolates\n",
        //  65 => "!000001000000004000Maracas de Peltre\n",
        //  66 => "\"000001000000004000Maracas de Aluminio\n",
        //  67 => " 000001000000004000Maracas Pesadas\n",
        //  68 => "#000005000000001000Caja de vainilla\n",
        //  69 => "!000001000000004000Maracas de goma\n",

        //  70 => "!100000580910000512Harina\n",
        //  71 => "!000000150000001500Jamon\n",
        //  72 => " 050540960300582661caracteres especiale\n",
        //  73 => "#000005000000001000Caja de Whisky\n",
        //  74 => "#000005000000001000Caja de Chocolates\n",
        //  75 => "!000001000000004000Maracas de Peltre\n",
        //  76 => "\"000001000000004000Maracas de Aluminio\n",
        //  77 => " 000001000000004000Maracas Pesadas\n",
        //  78 => "#000005000000001000Caja de vainilla\n",
        //  79 => "!000001000000004000Maracas de goma\n",

        //  80 => "!100000580910000512Harina\n",
        //  81 => "!000000150000001500Jamon\n",
        //  82 => " 050540960300582661caracteres especiale\n",
        //  83 => "#000005000000001000Caja de Whisky\n",
        //  84 => "#000005000000001000Caja de Chocolates\n",
        //  85 => "!000001000000004000Maracas de Peltre\n",
        //  86 => "\"000001000000004000Maracas de Aluminio\n",
        //  87 => " 000001000000004000Maracas Pesadas\n",
        //  88 => "#000005000000001000Caja de vainilla\n",
        //  89 => "!000001000000004000Maracas de goma\n",

        //  90 => "!100000580910000512Harina\n",
        //  91 => "!000000150000001500Jamon\n",
        //  92 => " 050540960300582661caracteres especiale\n",
        //  93 => "#000005000000001000Caja de Whisky\n",
        //  94 => "#000005000000001000Caja de Chocolates\n",
        //  95 => "!000001000000004000Maracas de Peltre\n",
        //  96 => "\"000001000000004000Maracas de Aluminio\n",
        //  97 => " 000001000000004000Maracas Pesadas\n",
        //  98 => "#000005000000001000Caja de vainilla\n",
        //  99 => "!000001000000004000Maracas de goma\n",

        //  100 => "!100000580910000512Harina\n",
        //  101 => "!000000150000001500Jamon\n",
        //  102 => " 050540960300582661caracteres especiale\n",
         
         10 => "101"
        );

        var_dump( $factura); 

          $file = "Factura.txt";	
            $fp = fopen($file, "w+");
            $write = fputs($fp, "");
                            
          foreach($factura as $campo => $cmd)
          {
            $write = fputs($fp, $cmd);
          }
                            
        fclose($fp); 
      
        $out =  $itObj->SendFileCmd($file);
  
        var_dump($out);
  
	  }elseif ($Foperacion == "Devolucion") {
	        $devolucion = array(-5 => "iS*Pedro Mendez\n",
							-4 => "iR*12.345.678\n",
							-3 => "iF*0000001\n",
			                -2 => "iI*Z4A1234567\n",
							-1 => "iD*18-01-2014\n",
							 0 => "d0000000100000001000Harina\n",
							 1 => "d1000000150000001500Jamon\n",
							 2 => "d2000000205000003000Patilla\n",
							 3 => "d3000005000000001000Caja de Wisky\n",
							 4 => "101");
							 
			$file = "NotaCredito.txt";	
                $fp = fopen($file, "w+");
                $write = fputs($fp, "");
                         
			foreach($devolucion as $campo => $cmd)
			{
		     	   $write = fputs($fp, $cmd);
			}
                        
      fclose($fp); 

      $out =  $itObj->SendFileCmd($file);

      var_dump($out);
	  }elseif ($Foperacion == "ReporteX") {
			$out =  $itObj->SendCmd("I0X");
	  }elseif ($Foperacion == "ReporteZ") {
			$out =  $itObj->SendCmd("I0Z");
	  }elseif ($Foperacion == "SetPort") {
		       $itObj->SetPort($_POST["PortName"]);
	  }	

   if($out == "ASK")
   {
       echo "<div align = 'center'><B><font color = 'green' size = '9'>TRUE</font></B></div>";
   }elseif($out == "NAK")
   {
       echo "<div align = 'center'><B><font color = 'red' size = '9'>FALSE</font></B></div>";
   }else
   {
      echo "<div align = 'center'>".$out."</div>";
   }
   	  
    // echo "<br><br><div align = 'center'>".$itObj->Log."</div>";

	//  <?php

	//  echo translateLine("X","87.112.252,00","8","bollitos de canela mas alla de lo evidente");
		
	 // Ejemplos comandos:
	 //detalle de 1
	 // real:  !100000580910000512Harina
	 // descompuesto: ! 1000005809 10000512 Harina
	 // (!) - tasa fiscal
	 // 10.000.058,09 - precio max cant de cifras 2 dec
	 // 10.000,512 - cant max cifras 3 decimales
	 // Harina - descripcion x digitos
		
	 // Ejemplos comandos: (otros a deconstruir)
	 // !100000580900000512Harina
	 // !000000150000001500Jamon
	 // "000000205000003000Patilla
	 // #000005000000001000Caja de Whisky
	 // #000005000000001000Caja de Chocolates
	 // !000001000000004000Maracas de Peltre
	 // "000001000000004000Maracas de Aluminio
	 // 101
		
	
	  }
		
	?>

 </div>

</BODY>
</HTML>
