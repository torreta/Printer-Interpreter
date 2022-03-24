# Printer-Interpreter
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
