net stop evtsys
copy /y evtsys.exe %systemroot%\system32
copy /y evtsys.dll %systemroot%\system32
net start evtsys
