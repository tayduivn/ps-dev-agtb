#!/bin/bash

# Ensure that the required mountpoint exists:
if [[ ! -d "/sugarcrm" ]]; then
    echo "You must mount your sugarcrm directory in the container as /sugarcrm."
    echo "Example:  docker run -v \$PWD:/sugarcrm ..."
    exit 1
fi

cd /sugarcrm

if [[ -z "${DEV}" ]]; then
    yarn install
    node_modules/gulp/bin/gulp.js test:rest "$@"
else
    /bin/bash
fi
