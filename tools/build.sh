#!/usr/bin/env bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )/../../web" && pwd )"

cd $DIR

if [ "$1" == "clean" ]; then
   php -r "require '../framework/inc.master.php'; \
           Minifier::clean();";
   echo "Cleaned"
else
   php -r "require '../framework/inc.master.php'; \
           Minifier::build();";
   echo "Completed"
fi
