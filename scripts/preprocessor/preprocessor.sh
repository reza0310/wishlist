#!/bin/sh

VENV_DIR=./venv
PYTHON=python3

if [ ! -d "$VENV_DIR" ]; then
    echo "Creating Python virtual environment ..."
    "$PYTHON" -m venv "$VENV_DIR"
    "$VENV_DIR/bin/activate"
    "$PYTHON" -m pip install -r requirements.txt
    echo ""
else
    "$VENV_DIR/bin/activate"
fi

"$PYTHON" preprocessor.py $@
result=$?

deactivate

exit $result
