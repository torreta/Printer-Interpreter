Dim WinScriptHost
Set objShell = WScript.CreateObject("WScript.Shell")
curDate = Day(Date)&"-"&Month(Date)&"-"&Year(Date)
dt = Hour(Time)&"-"&Minute(Time)
objShell.Run("C:\xampp\htdocs\Printer-Interpreter\starter.bat " & curDate & " " & dt), 0, True
Set WinScriptHost = Nothing
