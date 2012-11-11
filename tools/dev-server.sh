#!/usr/bin/env bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )/../../web" && pwd )"

cd $DIR

php -S localhost:8000 index.php

