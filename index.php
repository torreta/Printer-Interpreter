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

	// echo translateLine("X","87.112.252,00","8","bollitos de canela");
	
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
	
	// function translateTasa($tasa="", $tipo_doc=""){
	//   // de momento tengo entendido 4 tipos de tasa
	//   // (falta copiar de manuak, pero en ejemplo tengo)// (!), ("), (#), ( )
	
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
	// 		echo "Tasa no reconocida\n";
	// 		//  $comando = false;
	// 		$tasas = ["\"","!","#"," "];
	// 		$tasa = $tasas[array_rand($tasas, 1)];
	// 		$comando = $tasa;
			
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
	
	// function translatePrecio( $precio = ""){
	//   //validaciones de tipo precio
	//   // Precio del ítem (8 enteros + 2 decimales)
	
	//   // pico el numero en 2, quizas no se pique por ser un entero
	//   $precio = "12.6";
	
	//   if (is_numeric($precio) == false){
	// 	echo("valor invalido cifras\n");
	// 	return false;
	//   }else{
	// 	echo("valor numerico\n");
	//   }
	
	//   $cifras_separadas = explode(",",$precio); 
	
	  
	//   echo("valor entero \n". $cifras_separadas[0]. "\n");
	//   echo("valor decimal \n". $cifras_separadas[1]. "\n");
	
	//   $cant_cifras = count($cifras_separadas);
	  
	//   echo("cant cifras \n" . $cant_cifras."\n");
	//   if ($cant_cifras == 1){
	// 	echo("verificando cifras" . $cant_cifras);
	  
	
	//   }
	//   //else if()
	
	//   // strlen()
	
	
	//   var_dump($cifras_separadas);
	//   var_dump($cant_cifras);
	
	
	//   // to num from string
	//   //floatval($cifras_separadas);
	
	//  //$comando = floatval($cifras_separadas);
	
	//  return  $precio;
	// }
	
	// function translateCantidad($cant = ""){
	
	//  $comando = " cantidad";
	 
	//  return  $comando;
	// }
	
	// function translateDescription($desc = ""){
	
	//  $comando = " descripcion";
	 
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
