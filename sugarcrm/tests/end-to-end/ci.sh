#!/bin/bash

# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

set -xe


# You will need to run this script in the Mango directory
# e.g. cd Mango && ./sugarcrm/tests/end-to-end/ci.sh -u "${SUGAR_URL}"

CI_RESULTS_FOLDER_NAME='ci-results'
export CI_RESULTS_FOLDER_PATH=$(pwd)/${CI_RESULTS_FOLDER_NAME}

echo clean CI_RESULTS_FOLDER_NAME: ${CI_RESULTS_FOLDER_NAME}
rm -rf $CI_RESULTS_FOLDER_NAME
mkdir $CI_RESULTS_FOLDER_NAME
rm -f ${CI_RESULTS_FOLDER_NAME}.zip

SEEDBED_IMAGE_NAME='registry.sugarcrm.net/seedbed/seedbed'

# Tag is set to "latest" if not defined in shell

export SEEDBED_IMAGE_TAG='0_8'

SEEDBED_IMAGE_TAG="${SEEDBED_IMAGE_TAG:?latest}" 

echo 'LOG: Memory Info (start)'
  free -m

echo 'LOG: System Log (start)'
  dmesg -T

docker pull "${SEEDBED_IMAGE_NAME}:${SEEDBED_IMAGE_TAG}"

docker run \
   --rm \
   -v "${PWD}/sugarcrm:/sugarcrm" \
   -p 5900:5900 \
   --net=host \
   "${SEEDBED_IMAGE_NAME}:${SEEDBED_IMAGE_TAG}" "$@"

echo CI_RESULTS_FOLDER_PATH=${CI_RESULTS_FOLDER_PATH}

printf "${NC}create ${CI_RESULTS_FOLDER_NAME}.zip " && \
      zip -q -r ${CI_RESULTS_FOLDER_NAME}.zip $CI_RESULTS_FOLDER_NAME && \
      printf "${GREEN}success\n"

echo 'LOG: System Log (end)'
    dmesg -T

echo 'LOG: Memory Info (start)'
  free -m
