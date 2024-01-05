#!/bin/bash

PREPROCESSOR="$(pwd)/../../scripts/preprocessor/preprocessor.py"

terminate(){
    kill -s INT -- -$$
    exit
}

trap terminate SIGINT

if [ "$1" == "--watch" ]; then
    python3 "$PREPROCESSOR" private_html/ public/ --list pages.lst --override --watch &
    npm run serve &
    wait
else
    python3 "$PREPROCESSOR" private_html/ public/ --list pages.lst --override
    npm run build
fi
