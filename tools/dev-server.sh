#!/bin/bash
# Requires PHP 5.4+

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )/../../web" && pwd )"

cd $DIR

php -S localhost:8000 index.php

