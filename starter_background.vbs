Dim WinScriptHost

strComputer = "."

Set objWMIService = GetObject _
    ("winmgmts:\\" & strComputer & "\root\cimv2")
Set colProcessList = objWMIService.ExecQuery _
    ("Select * from Win32_Process Where Name = 'php.exe'")
For Each objProcess in colProcessList
    objProcess.Terminate()
Next

Set objShell = WScript.CreateObject("WScript.Shell")
curDate = Day(Date)&"-"&Month(Date)&"-"&Year(Date)
dt = Hour(Time)&"-"&Minute(Time)
objShell.Run("C:\xampp\htdocs\Printer-Interpreter\starter.bat " & curDate & " " & dt), 0, True
Set WinScriptHost = Nothing
