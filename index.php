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
<!-- <input name ="EnviarComando" type = "submit"  value="SubirS1" />
<input name ="EnviarComando" type = "submit"  value="SubirS2" />
<input name ="EnviarComando" type = "submit"  value="SubirS3"  />
<input name ="EnviarComando" type = "submit"  value="SubirS4" />
<input name ="EnviarComando" type = "submit"  value="SubirS5" />
<input name ="EnviarComando" type = "submit"  value="SubirS6"  />
<input name ="EnviarComando" type = "submit"  value="SubirU0X" />
<input name ="EnviarComando" type = "submit"  value="SubirU0Z" /> -->
</br>
</br>
<input name ="EnviarComando" type = "submit"  value="Facturar" />
<!-- <input name ="EnviarComando" type = "submit"  value="Devolucion" /> -->
</br>
</br>
<!-- <input name ="EnviarComando" type = "submit"  value="ReporteX" />
<input name ="EnviarComando" type = "submit"  value="ReporteZ" /> -->
</form>
</div>
<?php
	include_once ("TfhkaPHP.php"); 

         $Foperacion = null;
    if(isset($_POST["EnviarComando"]))
    { $Foperacion = $_POST["EnviarComando"]; }

    $itObj = new Tfhka();

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
	  }elseif ($Foperacion == "Facturar") {
	        $factura = array(
							// -5 => "iF*0000001\n",//factura asociadaj
							// -4 => "iI*Z4A1234567\n",// numero de control de esa factura
				            // -3 => "iD*18-01-2014\n",//fecha factura dia especifico
							// -2 => "iS*Pedro Mendez\n", // mombre persona
							// -1 => "iR*12.345.678\n", // rif
							 0 => "!100000580910000512Harina\n",
							 1 => "!000000150000001500Jamon\n",
							 2 => "\"000000205000003000Patilla\n",
							 3 => "#000005000000001000Caja de Whisky\n",
							 4 => "#000005000000001000Caja de Chocolates\n",
							 5 => "!000001000000004000Maracas de Peltre\n",
							 6 => "\"000001000000004000Maracas de Aluminio\n",
							 7 => " 000001000000004000Maracas Pesadas\n",
							 8 => "101"
							 );
		$file = "Factura.txt";	
                $fp = fopen($file, "w+");
                $write = fputs($fp, "");
                         
			foreach($factura as $campo => $cmd)
			{
		     	   $write = fputs($fp, $cmd);
			}
                        
                         fclose($fp); 
                         
                         $out =  $itObj->SendFileCmd($file);
                         
	  }elseif ($Foperacion == "Devolucion") {
	        $devolucion = array(-5 => "iS*Pedro Mendez\n",
							-4 => "iR*12.345.678\n",
							-3 => "iF*0000001\n",
			                -2 => "iI*Z4A1234567\n",
							-1 => "iD*18-01-2014\n",
							 0 => "d0000000100000001000Harina\n",
							 1 => "d1000000150000001500Jamon\n",
							 2 => 'd2000000205000003000Patilla\n',
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
   	  
	//echo "<br><br><div align = 'center'>".$itObj->Log."</div>";
	
	// <?php

	// echo translateLine("X","87.112.252,00","8","bollitos de canela mas alla de lo evidente");
	   
	// // Ejemplos comandos:
	// //detalle de 1
	// // real:  !100000580910000512Harina
	// // descompuesto: ! 1000005809 10000512 Harina
	// // (!) - tasa fiscal
	// // 10.000.058,09 - precio max cant de cifras 2 dec
	// // 10.000,512 - cant max cifras 3 decimales
	// // Harina - descripcion x digitos
	   
	// // Ejemplos comandos: (otros a deconstruir)
	// // !100000580900000512Harina
	// // !000000150000001500Jamon
	// // "000000205000003000Patilla
	// // #000005000000001000Caja de Whisky
	// // #000005000000001000Caja de Chocolates
	// // !000001000000004000Maracas de Peltre
	// // "000001000000004000Maracas de Aluminio
	// // 101
	   
	// function translateTasa($tasa=""){
	//   // de momento tengo entendido 4 tipos de tasa
	//   // (falta copiar de manuak, pero en ejemplo tengo)// (!), ("), (#), ( )
	   
	//   if($tasa == ""){
	//    echo("valor vacio de tasa\n");
	//    return false;
	//   }
   
	//   switch ($tasa) {
	// 	  case "Exento":
	// 		  echo "Exento\n";
	// 		  $comando = " ";
	// 		  break;
	// 	  case "Tasa 1":
	// 		  echo "Tasa 1\n";
	// 		  $comando = "!";
	// 		  break;
	// 	  case "Tasa 2":
	// 		  echo "Tasa 2\n";
	// 		  $comando = "\"";
	// 		  break;
	// 	  case "tasa 3":
	// 		  echo "Tasa 3\n";
	// 		  $comando = "#";
	// 		  break;
	// 	  default:
	// 	 echo "Tasa no reconocida\n";
	// 	 $comando = false;
	// 	 $tasas = ["\"","!","#"," "];
	// 	 $tasa = $tasas[array_rand($tasas, 1)];
	// 	 $comando = $tasa;
	//   }
	   
	//  return  $comando;
	// }
	   
	// // buscar expresion regular compatible con la mision de
	// // verificar si un numero es valido
	// // verificar si es valido sin decimales
	// // verificar si es valido con comas
	// // verificar si es valido con puntos
	// function validador_numerico($value){
	// 	 return (preg_match ('~^((?:\+|-)?[0-9]+)$~' ,$value) == 1);
	// }
	   
	// function padding_number_format($value="", $max_cifras="" ){
   
	//   // echo("valor precio\n" . $value . "\n");
	//   // echo("valor cifras\n" . $max_cifras . "\n");
   
   
	//   $cifras_padding = $max_cifras - strlen($value) ;
	//   // echo("padding cifras \n" . $cifras_padding . "\n");
   
	//   $padding ="";
   
	//   for ($i = 1; $i <= $cifras_padding; $i++) {
	//    $padding = $padding . "0";
	//   } 
   
	//   echo(" padding y valor: \n" . $padding . $value ." \n");
   
	// 	 return $padding . $value;
	//  }
   
	//  function padding_decimal_format($value="", $max_cifras="" ){
   
	//  //  echo("valor precio\n" . $value . "\n");
	//  //  echo("valor cifras\n" . $max_cifras . "\n");
   
	//   $cifras_padding = $max_cifras - strlen($value) ;
	//  // echo("padding cifras \n" . $cifras_padding . "\n");
   
	//   $padding ="";
   
	//   for ($i = 1; $i <= $cifras_padding; $i++) {
	//    $padding = $padding . "0";
	//   } 
   
	//   echo(" padding y valor: \n" . $value . $padding ." \n");
   
	// 	 return  $value. $padding;
	// }
   
	// function translatePrecio( $precio = ""){
	//   //validaciones de tipo precio
	//   // Precio del ítem (8 enteros + 2 decimales)
	//   $enteros = "";   // 8 siempre, cualquier numero + relleno en ceros
	//   $decimales = ""; // 2 siempre, cualquier numero + relleno en ceros 
   
	//   // pico el numero en 2, quizas no se pique por ser un entero
	//   $precio = "12.6";
		 
	//   if($precio == ""){
	//    echo("valor vacio de precio\n");
	//    return false;
	//   }
   
	//    // aqui va la funcion expresion regular validador de numeros
	//   if (is_numeric($precio) == false){
	//    echo("valor invalido cifras\n" + $precio );
	//    return false;
	//   }else{
	// 	  echo("valor numerico\n");
	//   }
   
	//   // se hace esto porque la cifra y los decimales en la traduccion no tienen
	//   // ningun tipo de marcacion, solo se asume que son los ultimos 2 digitos los decimales
	  
	//   // separo la cifra en 2 pedazos, entero y decimal para poder evaluarlo aparte
	//   $cifras_separadas = explode(".",$precio); 
   
	//   // evaluo en la cantidad de pedazos en que se pico el numero, si es anormal se descarta
	//   $cant_cifras = count($cifras_separadas);
		 
	//   echo("cant cifras \n" . $cant_cifras."\n");
   
	//    // con solo parte entera tengo que agregar padding decimal
	//    // y tengo que completar lo que sea el numero entero a 8 digitos con padding
	//    // de ceros.
   
	//    switch ($cant_cifras) {
	// 	 case 1:
	// 	   // con solo parte entera tengo que agregar padding decimal
	// 	   echo "solo numero sin decimales\n";
	// 	   echo("valor entero\n ". $cifras_separadas[0] . "\n");
	// 	   $decimales = "00";
	// 	   $enteros = padding_number_format($cifras_separadas[0],8);
	// 	   echo($enteros);
	// 	   break;
	// 	 case 2:
	// 	   // 
	// 	   echo "numero + decimales\n";
	// 	   echo("valor entero\n ". $cifras_separadas[0] . "\n");
	// 	   echo("valor decimal\n ". $cifras_separadas[1] . "\n");
		   
	// 	   $enteros = padding_number_format($cifras_separadas[0],8);
	// 	   $decimales = padding_decimal_format($cifras_separadas[1],2);  
   
	// 	   break;
	// 	 default:
	// 	   echo "formato de numero no reconocido\n";
	// 	   return false;
	//    }
	   
	//    return  $enteros.$decimales;
	// }
	   
	// function translateCantidad($cant = ""){
	//   //validaciones de cantidad
	//   // cantidad del ítem (5 enteros + 3 decimales)
	//   $enteros = "";   // 5 siempre, cualquier numero + relleno en ceros
	//   $decimales = ""; // 3 siempre, cualquier numero + relleno en ceros 
   
	//   // pico el numero en 2, quizas no se pique por ser un entero
	//   $cant = "1250.955";
		 
	//   if($cant == ""){
	//    echo("valor vacio de cantidad\n");
	//    return false;
	//   }
   
	//    // aqui va la funcion expresion regular validador de numeros
	//   if (is_numeric($cant) == false){
	//    echo("valor invalido cifras\n" + $cant );
	//    return false;
	//   }
   
	//   // se hace esto porque la cifra y los decimales en la traduccion no tienen
	//   // ningun tipo de marcacion, solo se asume que son los ultimos 3 digitos son decimales
	  
	//   // separo la cifra en 2 pedazos, entero y decimal para poder evaluarlo aparte
	//   $cifras_separadas = explode(".",$cant); 
   
	//   // evaluo en la cantidad de pedazos en que se pico el numero, si es anormal se descarta
	//   $cant_cifras = count($cifras_separadas);
		 
	//   echo("cant cifras \n" . $cant_cifras."\n");
   
	//    // con solo parte entera tengo que agregar padding decimal
	//    // y tengo que completar lo que sea el numero entero a 5 digitos con padding
	//    // de ceros.
   
	//    switch ($cant_cifras) {
	// 	 case 1:
	// 	   // con solo parte entera tengo que agregar padding decimal
	// 	   echo "solo numero sin decimales\n";
	// 	   echo("valor entero\n ". $cifras_separadas[0] . "\n");
	// 	   $decimales = "000";
	// 	   $enteros = padding_number_format($cifras_separadas[0],5);
	// 	   echo($enteros);
	// 	   break;
	// 	 case 2:
	// 	   // 
	// 	   echo "numero + decimales\n";
	// 	   echo("valor entero\n ". $cifras_separadas[0] . "\n");
	// 	   echo("valor decimal\n ". $cifras_separadas[1] . "\n");
		   
	// 	   $enteros = padding_number_format($cifras_separadas[0],5);
	// 	   $decimales = padding_decimal_format($cifras_separadas[1],3);  
   
	// 	   break;
	// 	 default:
	// 	   echo "formato de numero no reconocido cantidad\n";
	// 	   return false;
	//    }
	   
	//    return  $enteros.$decimales;
   
	// }
	   
	// function translateDescription($desc = ""){
	 
	//    $max_caracteres = 20; //definido en el manual
   
	//    $comando = substr($desc,0,$max_caracteres);
   
	//  return  $comando;
	// }
	   
	   
	// function translateLine( $tasa="", $precio = "", $cant = "", $desc = "",$tipo_doc=""){
	   
	//  $comando = translateTasa($tasa, $tipo_doc) .translatePrecio($precio) . translateCantidad($cant) .translateDescription($desc);
	   
	//   echo "\n\nComando Final\n"; 
	   
	//  return  $comando;
	// }
   
	
 </div>

</BODY>
</HTML>
