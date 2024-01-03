@echo off

set VENV_DIR=venv
set PYTHON=python

if exist "%VENV_DIR%\" (
    call "%VENV_DIR%\Scripts\activate.bat"
) else (
    echo Creating Python virtual environment ...
    "%PYTHON%" -m venv "%VENV_DIR%"
    call "%VENV_DIR%\Scripts\activate.bat"
    "%PYTHON%" -m pip install -r requirements.txt
    echo.
)

"%PYTHON%" preprocessor.py %*

deactivate
