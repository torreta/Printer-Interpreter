# Instalación Printer-Interpreter para POS (Producción)
Este documento explica cómo instalar el Printer-Interpreter en la máquina donde va a estar la impresora fiscal. Para ser implementado con el sistema STRIX POS.

## Requisitos Previos
- Token de autenticación para clonar el repositorio del Printer-Interpreter.
- Asegurarse de que está corriendo el API de STRIX - POS
- Obtener dirección IP de la máquina donde está corriendo el API. Así como también, el usuario, la contraseña de la base de datos, y el puerto de MySQL
- Conectar la impresora fiscal a la máquina del cliente
- Pendrive para almacenar los directorios necesarios para la instalación
- Descargar el ejecutable de instalación de Visual Studio  **VSCodeUserSetup-x64-1.86.0.exe** en el siguiente link y almacenarlo en el pendrive 
  
https://drive.google.com/drive/folders/1ecjGhIbbgbdS2TOAl-vyrqS79CnhBlKQ?usp=drive_link
- Descargar el ejecutable de instalación de XAMPP  **xampp-windows-x64-8.0.30-0-VS16-installer.exe** en el siguiente link y almacenarlo en el pendrive 

https://drive.google.com/drive/folders/1ecjGhIbbgbdS2TOAl-vyrqS79CnhBlKQ?usp=drive_link
- Descargar el directorio de la aplicación **IntFHKA** en el siguiente link y almacenarlo en el pendrive 
  
https://drive.google.com/drive/folders/1ecjGhIbbgbdS2TOAl-vyrqS79CnhBlKQ?usp=drive_link
- Descargar el directorio de la aplicación **IMPRIMIR.lnk** en el siguiente link y almacenarlo en el pendrive 
  
https://drive.google.com/drive/folders/1ecjGhIbbgbdS2TOAl-vyrqS79CnhBlKQ?usp=drive_link
## Pasos para la instalación
### I. Preparación en el entorno local (Linux)
1. Clonar el repositorio donde se desarrolla la aplicación Printer-Interpreter en la máquina local
```
git clone https://github.com/torreta/Printer-Interpreter.git
```
2. Debemos posicionarnos en la rama de Git en la que va a operar la aplicación hoy en día (06/02/2024) es la rama ```feature/insert-ledger```. Le pedirá un ```token``` de autenticación debe solicitarlo a los administrador del repo de Strix Technologies (Luis Campos)
```
git checkout feature/insert-ledger
```
3. Verificar que no haya algún cambio que no se haya actualizado con
```
git pull
```
4. Copiar este directorio  ```Printer-Interpreter``` dentro del pendrive para utilizarlo al momento de instalar en la máquina del cliente

### II. Instalación de archivos y directorios en la máquina del cliente (Conectada con la impresora fiscal)
1. Instalar Visual Studio con el archivo **VSCodeUserSetup-x64-1.86.0.exe** que debería de haberse descargado anteriormente del link que se especifica en **REQUISITOS**. Ejecutarlo y aceptar todo.
2. Instalar XAMPP con el archivo **xampp-windows-x64-8.0.30-0-VS16-installer.exe** que debería de haberse descargado anteriormente del link que se especifica en **REQUISITOS**. Ejecutarlo y aceptar todo.
3. El directorio **IntFHKA** que debería de haberse descargado anteriormente del link que se especifica en **REQUISITOS**, debe ubicarlo en la raíz del Disco Duro ```C:\```
4. El proyecto **Printer-Interpreter** que debería de haberse descargado en el pendrive anteriormente, con las instrucciones de la primera sección  **Preparación en el entorno local**, debe ubicarlo en el directorio de ```htdocs``` de XAMPP ```C:\xampp\htdocs\```
5. El archivo **IMPRIMIR.lnk** que debería de haberse descargado en el pendrive anteriormente, con las instrucciones de la sección  **REQUISITOS**, debe ubicarlo en el Escritorio/Desktop donde lo pueda visualizar el usuario.

### III. Configuración de los archivos instalados
#### Configuración base de datos de Printer-Interpreter
1. En el proyecto que ubicamos en el directorio de ```htdocs``` de XAMPP ```C:\xampp\htdocs\``` debemos configurar el archivo ```DBConfig.php``` donde debemos indicar
- ```DB_NAME```: Nombre que se le vaya a asignar a la base de datos
- ```DB_PORT```: Dirección IP del servidor que está atendiendo las peticiones con el API
- ```DB_USER```: Admin que ha asignado en MySQL
- ```DB_PASSWORD```: Contraseña del admin asignado en MySQL

Quedando de la siguiente manera
```
<?php
  /*** (B) SETTINGS ***/
  // Database settings 
  // change these to your own
  // Do a copy of this file without the .example as namefile
  define('DB_HOST', 'strixerp.gotdns.com');
  define('DB_NAME', 'pos_ejemplo');
  define('DB_PORT', '3306');
  define('DB_CHARSET', 'utf8');
  define('DB_USER', 'adminejemplo'); // remember // anibal
  define('DB_PASSWORD', 'passwordejemplo'); // ask // man***
?>
```
2. Para verificar si se ha configurado correctamente los parámetros de la base de datos. Puede ejecutar el archivo **IMPRIMIR.lnk**. Si este no ejecuta correctamente, debe verificar la ubicación de los directorios y archivos explicados anteriormente, y los parámetros asignados en el archivo **DBConfig.php**
   
#### Configuración de puerto COM en Printer-Interpreter e IntFHKA
1. Al haber conectado la impresora fiscal en la máquina. Debe verificar en qué puerto USB está conectado. Para ello debe utilizar el navegador de Windows para buscar la configuración de **Administrador de Dispositivos** en el apartado **USB** verá los dispositivos conectados identificados con la palabra clave ```COM```
2. Al saber el puerto COM que ha tomado la impresora fiscal, debe dirigirse al archivo ```Puerto.dat``` ubicado en el directorio **IntFHKA** que movimos a la raíz del disco duro. Y debe configurar el puerto donde se encuentra la impresora quedando de la siguiente manera
```
COM3
```
- Aquí debe ingresar el puerto consultado anteriormente

3. Al saber el puerto COM que ha tomado la impresora fiscal, debe dirigirse al archivo ```Puerto.dat``` ubicado en el proyecto **Printer-Interpreter** que movimos al directorio ```htdocs``` de XAMPP ```C:\xampp\htdocs\```. Y debe configurar el puerto donde se encuentra la impresora quedando de la siguiente manera
```
COM3
```
- Aquí debe ingresar el puerto consultado anteriormente
4. Debe cerciorarse que los puertos configurados son iguales, para que pueda funcionar correctamente. Para ello puede ejecutar la aplicación XAMPP y habilitar los servicios APACHE
2. 

## Printer-Interpreter
First Steps toward a Spooler for a fiscal Project, we need to talk with the printer. and make it more human readable as a php object so the main project can talk to it.

1. mainly have the IntTFHKA libraries / emulator / com0com / serial port monitor installed

2. get the printer port code, so it configures the  printer emulator properly
   (1 digit COM<numberPort>) or wont work, use nullports if you need to replicate
   interactions from a higher port number. (and set that number on puerto.dat file)

3. configure starter.bat paths depending on ur current machine.

4. follow examples how to configure paths on daemon.php final lines

5. configure daemon database conection on the same DBConfig.php file (make a copy from the .example)

6. after all is configured you can run starter.vbs to start the daemon as a background process

7. to kill the deamon u just need to kill the CLI (php) process

8. requires PHP 7+

9. emulator require JVM version 7u76 (or it becomes a hell debugging)

10. its necesary to configure decimals and command parameters too on the printerSetting.php File
    theres an example already set...
