#!/bin/sh

npm run build
preprocessor private_html/ public/ --list pages.lst --override
