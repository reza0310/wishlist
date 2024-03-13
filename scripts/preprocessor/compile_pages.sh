#!/bin/sh
python3 WEBPAGES_compiler.py $@
python3 CSS_compiler.py $@
exit $?
