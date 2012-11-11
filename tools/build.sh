#!/usr/bin/env bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )/../../web" && pwd )"

cd $DIR

if [ ! -d "../libraries/yuicompressor/build" ]; then
   echo "Missing required package: libraries/yuicompressor"
   exit 1
fi

if [ "$1" == "clean" ]; then
   php -r "require '../framework/class.cache.php'; \
           require '../framework/class.minifier.php'; \
           Minifier::clean();";
   echo "Cleaned"
else
   php -r "require '../framework/class.cache.php'; \
           require '../framework/class.minifier.php'; \
           Minifier::build();";
   echo "Completed"
fi
