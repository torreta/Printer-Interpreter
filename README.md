# Printer-Interpreter
First Steps toward a Spooler for a fiscal Project, we need to talk with the printer. and make it more human readable as a php object so the main project can talk to it.

1. mainly have the IntTFHKA libraries / emulator / com0com / port monitor installed

2. get their test code, so it configures the emulator printer por properly
   (1 digit COM<numberPort>) or wont work, use nullports if you need to replicate
   interactions from a higher number port.

3. this only works on localhost for now, since im using / writing files.

4. requires PHP 7+

5. configure starter.bat paths depending on ur current machine.

6. follow examples how to configure paths on daemon.php final lines

7. configure daemon database conection on the same daemon.php file

8. after all is configured you can run starter.vbs to start the daemon a background process
