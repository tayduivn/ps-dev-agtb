# Setup Default Variables
SUGAR_BUILD_NUMBER ?= 999
SUGAR_FLAVOR ?= ent
SUGAR_ENV ?= dev
SUGAR_TRANSLATIONS_SCM ?= false

SHELL:=bash
# hackish way to make sure that the package version is Ent and not ent
PACKAGE_FLAVOR=$(shell echo $(SUGAR_FLAVOR) | cut -c1 | tr [a-z] [A-Z])
PACKAGE_FLAVOR:=$(PACKAGE_FLAVOR)$(shell echo $(SUGAR_FLAVOR) | cut -c2- | tr [A-Z] [a-z])

## Set the package name, this can be overridden
SUGAR_PACKAGE_NAME ?= Sugar$(PACKAGE_FLAVOR)-$(SUGAR_VERSION)
SUGAR_PACKAGE_FOLDER ?= Sugar$(PACKAGE_FLAVOR)-Full-$(SUGAR_VERSION)

# if the translations dir is here, include it as part of the build
ifneq (,$(wildcard ${CURDIR}/translations/.))
__INCLUDE_LATIN := --latin=1
ifeq ($(SUGAR_TRANSLATIONS_SCM), false)
__INCLUDE_LATIN +=  --no-latin-scm
endif
endif

# V := 1 # When V is set, print commands and build progress.
Q := $(if $V,,@)

.DEFAULT_GOAL := build

.PHONY: help
help:
	@echo "Example:"
	@echo " SUGAR_VERSION=7.10.0.0 SUGAR_BUILD_DIR=/var/www/html make clean build"
	@echo ""
	@echo "ENVIRONMENT VARS:"
	@echo " SUGAR_VERSION=            Version number to build, Required"
	@echo " SUGAR_BUILD_DIR=          Where to build to, Required"
	@echo " SUGAR_BUILD_NUMBER=       build number for this build, used in ci. Default: $(SUGAR_BUILD_NUMBER)"
	@echo " SUGAR_FLAVOR=             what flavor of sugar to build. Default: $(SUGAR_FLAVOR)"
	@echo " SUGAR_ENV=                set to production when building the release packages Default: $(SUGAR_ENV)"
	@echo " SUGAR_TRANSLATIONS_SCM=   when true, will trigger the SCM interactions in translations Default: $(SUGAR_TRANSLATIONS_SCM)"
	@echo ""
	@echo "TARGETS:"
	@echo " build:                  builds Sugar for Dev Use"
	@echo " release_build:          builds Sugar and Upgrade Utilities"
	@echo " docker_build:           builds Sugar in a docker container, runs `make clean build`
	@echo " docker_release_build:   builds Sugar and Upgrade Utilities in a docker container, runs `make clean release_build`
	@echo " clean:                  removes existing build from the SUGAR_BUILD_DIR"
	@echo " package:                creates the zip files and places them in SUGAR_BUILD_DIR, should be called after build or release_build"

# the basic build to build locally
.PHONY: build
build: check rome post_build files_md5

# build a release build, this just adds the upgrade utilities before cleanup
.PHONY: release_build
release_build: check rome post_build upgrade_utilities files_md5

# package will actually create the release packages
# - this moves the flavor folder into the correct folder for the zips
# - then it creates the zip file and the tar.gz file
# - then it moves the folder back to the flavor instead of the zip folder
.PHONY: package
package: package_tests package_clean files_md5
	@echo "=====> Packaing Build <====="
	$Q cd ${SUGAR_BUILD_DIR}; mv ${SUGAR_FLAVOR} ${SUGAR_PACKAGE_FOLDER}
	$Q cd ${SUGAR_BUILD_DIR}; zip -r -q ${SUGAR_BUILD_DIR}/${SUGAR_PACKAGE_NAME}.zip ${SUGAR_PACKAGE_FOLDER}
	$Q cd ${SUGAR_BUILD_DIR}/${SUGAR_PACKAGE_FOLDER}; tar cpzf ${SUGAR_BUILD_DIR}/${SUGAR_PACKAGE_NAME}.tar.gz .
	$Q cd ${SUGAR_BUILD_DIR}; mv ${SUGAR_PACKAGE_FOLDER} ${SUGAR_FLAVOR}
	@echo "=====> Packages Can Be Found In: $(SUGAR_BUILD_DIR) <====="

# grouping the cleanup utilites, this should not be run out side the release_build as the clean up removes
# the health check
.PHONY: upgrade_utilities
upgrade_utilities: build_shadowupgrade build_silentupgrade build_sortinghat

# make sure that the required env vars are set
.PHONY: check
check:
ifndef SUGAR_VERSION
	$(error "SUGAR_VERSION not defined")
endif
ifndef SUGAR_BUILD_DIR
	$(error "SUGAR_BUILD_DIR not defined")
endif

.PHONY: clean
clean:
	@echo "=====> Removing Existing Build <===="
	$Q rm -rf ${SUGAR_BUILD_DIR}/${SUGAR_FLAVOR}

# Before we run rome, what steps should be run
# if there is something else to run here, then put it here, but currently nothing mango related
.PHONY: pre_build
pre_build: composer_install build_sidecar

# Run Rome
.PHONY: rome
rome: pre_build
	@echo "=====> Running Rome <====="
	$Q cd build/rome && php build.php -ver=${SUGAR_VERSION} --flav=${SUGAR_FLAVOR} --base_dir=${CURDIR}/sugarcrm --build_dir=${SUGAR_BUILD_DIR} --build_number=${SUGAR_BUILD_NUMBER} --clean=0 ${__INCLUDE_LATIN}

	$Q touch ${SUGAR_BUILD_DIR}/${SUGAR_FLAVOR}/config.php
	$Q touch ${SUGAR_BUILD_DIR}/${SUGAR_FLAVOR}/config_override.php
	$Q touch ${SUGAR_BUILD_DIR}/${SUGAR_FLAVOR}/.htaccess

# Post build tasks
.PHONY: post_build
post_build:
	@echo "=====> Post Build <====="

# Cleanup Tasks
.PHONY: cleanup
cleanup:
	@echo "=====> Build Clean Up <====="
	$Q rm -rf ${SUGAR_BUILD_DIR}/${SUGAR_FLAVOR}/modules/HealthCheck
	$Q rm -f ${SUGAR_BUILD_DIR}/${SUGAR_FLAVOR}/portal2/index.php
	$Q rm -rf ${SUGAR_BUILD_DIR}/${SUGAR_FLAVOR}/tests/{old}/HealthCheck
	$Q rm -rf ${SUGAR_BUILD_DIR}/${SUGAR_FLAVOR}/tests/{old}/modules/HealthCheck

# Create the hash map of files and their md5 hash
.PHONY: files_md5
files_md5: cleanup
	@echo "=====> File md5 Hash <====="
	$Q php ${CURDIR}/build/utilities/files_md5.php --path ${SUGAR_BUILD_DIR}/${SUGAR_FLAVOR}

# Install Composer
.PHONY: composer_install
composer_install:
ifeq ($(SUGAR_ENV), production)
	@echo "=====> Composer Install Production <====="
	$Q cd sugarcrm && composer install --optimize-autoloader --no-dev -o
else
	@echo "=====> Composer Install <====="
	$Q cd sugarcrm && composer install -o
endif
	$Q rm -f ./sugarcrm/vendor/autoload.php

# Build Sidecar
.PHONY: build_sidecar
build_sidecar:
	$Q cd sugarcrm/sidecar; make

# Build the silent upgrade packages
.PHONY: build_silentupgrade
build_silentupgrade:
	$Q cd ${SUGAR_BUILD_DIR}/${SUGAR_FLAVOR}/modules/UpgradeWizard && php pack_cli.php ${SUGAR_BUILD_DIR}/silentUpgrade-PRO-${SUGAR_VERSION} ${SUGAR_VERSION} ${SUGAR_BUILD_NUMBER}

# build the shadow upgrade packages
.PHONY: build_shadowupgrade
build_shadowupgrade:
	$Q cd ${SUGAR_BUILD_DIR}/${SUGAR_FLAVOR}/modules/UpgradeWizard && php pack_shadow.php ${SUGAR_BUILD_DIR}/shadowUpgrade-${SUGAR_VERSION} ${SUGAR_VERSION} ${SUGAR_BUILD_NUMBER}

# build sortinghat packages
.PHONY: build_sortinghat
build_sortinghat:
	$Q cd ${SUGAR_BUILD_DIR}/${SUGAR_FLAVOR}/modules/HealthCheck && php pack_sortinghat.php ${SUGAR_BUILD_DIR}/sortinghat-${SUGAR_VERSION}.phar ${SUGAR_VERSION} ${SUGAR_BUILD_NUMBER}

# create the test packages
.PHONY: package_tests
package_tests:
	@echo "=====> Creating Tests Package: Sugar${PACKAGE_FLAVOR}-${SUGAR_VERSION}-tests.zip <====="
	$Q cd ${SUGAR_BUILD_DIR}/${SUGAR_FLAVOR}; zip -9 -q --recurse-paths ${SUGAR_BUILD_DIR}/Sugar${PACKAGE_FLAVOR}-${SUGAR_VERSION}-tests.zip tests sidecar/tests portal2/tests gulp gulpfile.js
ifeq ($(SUGAR_FLAVOR), ent)
	@echo "=====> Creating Legacy Test Package: tests.zip <====="
	$Q cd ${SUGAR_BUILD_DIR}/${SUGAR_FLAVOR}; zip -9 -q --recurse-paths ${SUGAR_BUILD_DIR}/tests.zip tests
endif

# Additional Cleanup for Packages
.PHONY: package_clean
package_clean:
	@echo "=====> Additonal Cleanup For Packages <====="
	$Q cd ${SUGAR_BUILD_DIR}/${SUGAR_FLAVOR}; rm -rf tests
	$Q cd ${SUGAR_BUILD_DIR}/${SUGAR_FLAVOR}; rm -rf sidecar/tests
	$Q cd ${SUGAR_BUILD_DIR}/${SUGAR_FLAVOR}; rm -rf portal2/tests


# Handle the docker images
.PHONY: build_docker_image push_docker_image docker_build

DOCKER_REGISTRY = registry.sugarcrm.net
DOCKER_REGISTRY_REPO = mango
DOCKER_REGISTRY_IMAGE = build
DOCKER_REGISTRY_TAG ?= latest

DOCKER_IMAGE = $(DOCKER_REGISTRY)/$(DOCKER_REGISTRY_REPO)/$(DOCKER_REGISTRY_IMAGE):$(DOCKER_REGISTRY_TAG)

# build sugar inside of a docker container, this will be used in CI, but it's a lot slower
# on client machines
docker_build: check
	@echo "=====> Building Sugar In Docker Container <====="
	$Q docker run -it --rm \
		-e SUGAR_VERSION=$(SUGAR_VERSION) \
		-e SUGAR_FLAVOR=$(SUGAR_FLAVOR) \
		-e SUGAR_BUILD_NUMBER=$(SUGAR_BUILD_NUMBER) \
		-e SUGAR_ENV=$(SUGAR_ENV) \
		-e SUGAR_PACKAGE_FOLDER=$(SUGAR_PACKAGE_FOLDER) \
		-e SUGAR_PACKAGE_NAME=$(SUGAR_PACKAGE_NAME) \
		-v $(CURDIR):/sugar \
		-v $(SUGAR_BUILD_DIR):/build \
		$(DOCKER_IMAGE) clean build

# build sugar inside of a docker container, this will be used in CI, but it's a lot slower
# on client machines
docker_release_build: check
	@echo "=====> Building Sugar In Docker Container <====="
	$Q docker run -it --rm \
		-e SUGAR_VERSION=$(SUGAR_VERSION) \
		-e SUGAR_FLAVOR=$(SUGAR_FLAVOR) \
		-e SUGAR_BUILD_NUMBER=$(SUGAR_BUILD_NUMBER) \
		-e SUGAR_ENV=$(SUGAR_ENV) \
		-e SUGAR_PACKAGE_FOLDER=$(SUGAR_PACKAGE_FOLDER) \
		-e SUGAR_PACKAGE_NAME=$(SUGAR_PACKAGE_NAME) \
		-v $(CURDIR):/sugar \
		-v $(SUGAR_BUILD_DIR):/build \
		$(DOCKER_IMAGE) clean release_build

# This step is a bit more complicated than it should be, we have to create a temp directroy so
# it doesn't include all the mango files while building since they are not need for this image
BUILD_DOCKER_TMP_DIR=/tmp/mango_build_docker_image
build_docker_image:
	@echo "=====> Building Docker Build Image <====="
	$Q rm -rf $(BUILD_DOCKER_TMP_DIR)
	$Q mkdir -p $(BUILD_DOCKER_TMP_DIR)
	$Q cp ./Dockerfile.build $(BUILD_DOCKER_TMP_DIR)
	$Q cd $(BUILD_DOCKER_TMP_DIR); docker build -t $(DOCKER_IMAGE) -f Dockerfile.build .
	$Q rm -rf $(BUILD_DOCKER_TMP_DIR)

# publish the docker image to the registry, please take great care when doing this
# as you could break the build system
publish_docker_image:
	@echo "=====> Publishing Docker Build Image <====="
	$Q docker push $(DOCKER_IMAGE)
