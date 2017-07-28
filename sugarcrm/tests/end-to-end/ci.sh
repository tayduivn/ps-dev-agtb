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

SEEDBED_IMAGE_NAME='registry.sugarcrm.net/seedbed/seedbed'

# Tag is set to "latest" if not defined in shell
# export SEEDBED_IMAGE_TAG='custom' to override latest
SEEDBED_IMAGE_TAG="${SEEDBED_IMAGE_TAG:?latest}" 

docker pull "${SEEDBED_IMAGE_NAME}:${SEEDBED_IMAGE_TAG}"

docker run \
   --rm \
   -v "${PWD}/sugarcrm:/sugarcrm" \
   -p 5900:5900 \
   --net=host \
   "${SEEDBED_IMAGE_NAME}:${SEEDBED_IMAGE_TAG}" "$@"
