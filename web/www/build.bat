@echo off

SET "PREPROCESSOR=%~dp0\..\..\scripts\preprocessor\preprocessor.py"

IF "%~1"=="--watch" (
    start /B "webpack_dev_server" npm run serve
    python "%PREPROCESSOR%" private_html/ public/ --list pages.lst --override --watch
    rem taskkill /fi "WindowTitle eq webpack_dev_server"
    wmic process where "name like '%%node.exe%%' and commandline like '%%run serve%%'" delete
) else (
    python "%PREPROCESSOR%" private_html/ public/ --list pages.lst --override
    npm run build
)
