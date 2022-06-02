<?php

  include_once ("Utils.php"); 
  include_once ("Configs.php"); 

class status_interpreter{


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



}
?>