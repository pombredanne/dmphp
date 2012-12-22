#!/usr/bin/env bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )/../../web" && pwd )"

cd $DIR

php -S 0.0.0.0:8000 index.php

