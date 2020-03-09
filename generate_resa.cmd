SET directory="%~dp0"
SET distDir=%UserProfile%\Desktop\resa-online\


echo %distDir%
mkdir %distDir%
REM API
mkdir %distDir%\api
xcopy /e %directory%\api %distDir%\api
REM Controllers
mkdir %distDir%\controller
xcopy /e %directory%\controller %distDir%\controller
REM Includes
mkdir %distDir%\includes
xcopy /e %directory%\includes %distDir%\includes
REM Languages
mkdir %distDir%\languages
xcopy /e %directory%\languages %distDir%\languages
REM another files
copy %directory%includes.php %distDir%
copy %directory%main.php %distDir%
copy %directory%ReadMe.txt %distDir%
REM attachments dir
mkdir %distDir%\attachments
del %distDir%\languages\*.po
del %distDir%\languages\*.pot
del %distDir%\controller\RESA_SystemInfoController.php
rmdir /S /Q %distDir%\controller\css\css_custom

mkdir %distDir%\ui
call generate_angular.cmd && xcopy /e %directory%ui %distDir%\ui
del %distDir%\ui\index.php

pause;
