#!/usr/bin/env bash

# Where is your repo at?
SUGAR_DIR=$HOME/Mango

if [ ! -d ${SUGAR_DIR} ]; then
echo "Sugar Project Not found, Please Fix"
exit
fi

# We want to build to
BUILD_DIR=/Applications/MAMP/htdocs

if [ ! -d ${BUILD_DIR} ]; then
echo "Build Directory Not found, Please Fix"
exit
fi

# Don't include the trailing /
HOST_URL="http://localhost:8888"

#default version. It will use this if the branch name doesn't start with a version (ex 6_4_3)
DEFAULT_VER=6.4.2

#which edition to build
FLAV=ent

# Lets start the build here

cd ${SUGAR_DIR}/build/rome/

BRANCH=`git branch | grep '*' | cut -b3-`

case "$BRANCH" in
*)
VER=${DEFAULT_VER}
DIR=${BRANCH}
;;
esac

# try and override the version if it's set in the branch name
REGEX='([0-9])_([0-9])_([0-9])';
if [[ $BRANCH =~ $REGEX ]]; then
i=1
n=${#BASH_REMATCH[*]}
while [[ $i -lt $n ]]
do
BR=("${BR[@]}" "${BASH_REMATCH[$i]}")
let i++
done
SAVE_IFS=$IFS
IFS="."
VER="${BR[*]}"
IFS=$SAVE_IFS
fi

BUILD_DIR=${BUILD_DIR}/$DIR


function backup {
if [ -d ${BUILD_DIR}/${FLAV}/$1 ]; then
cp -r ${BUILD_DIR}/${FLAV}/$1 /tmp/buildstuff-${DIR}/$1
elif [ -f ${BUILD_DIR}/${FLAV}/$1 ]; then
cp ${BUILD_DIR}/${FLAV}/$1 /tmp/buildstuff-${DIR}/$1
fi
}

function restore {
if [ -d /tmp/buildstuff-${DIR}/$1 ]; then
cp -r /tmp/buildstuff-${DIR}/$1 ${BUILD_DIR}/${FLAV}/$1
elif [ -f /tmp/buildstuff-${DIR}/$1 ]; then
cp /tmp/buildstuff-${DIR}/$1 ${BUILD_DIR}/${FLAV}/$1
fi
}

if [ $# -lt 1 ]
then
rm -rf /tmp/buildstuff-${DIR}
mkdir /tmp/buildstuff-${DIR}
backup .htaccess
backup config.php
backup custom
php build.php --ver=${VER} --flav=${FLAV} --base_dir=${SUGAR_DIR}/sugarcrm --build_dir=${BUILD_DIR} --clean=1
restore .htaccess
restore config.php
restore custom

SIDECAR_DIR="${SUGAR_DIR}/sidecar"
if [ -d ${SIDECAR_DIR} ]; then
echo -n "Installing SideCar..."
# if sidecar exist, copy out the config and remove the dir
SIDECAR_CONFIG="${BUILD_DIR}/ent/config.js"
if [ -d ${BUILD_DIR}/sidecar ]; then
if [ -f ${SIDECAR_CONFIG} ]; then
cp ${SIDECAR_CONFIG} /tmp/buildstuff-${DIR}/sidecar_config.js
fi
rm -rf ${BUILD_DIR}/sidecar
fi
cp -r ${SIDECAR_DIR} ${BUILD_DIR}

if [ -f /tmp/buildstuff-${DIR}/sidecar_config.js ]; then
cp /tmp/buildstuff-${DIR}/sidecar_config.js ${SIDECAR_CONFIG}
else
sed "s/serverUrl: '..\/..\/sugarcrm/serverUrl: '\/${DIR}\/${FLAV}/" ${BUILD_DIR}/sidecar/extensions/core/sampleConfig.js > ${SIDECAR_CONFIG}
fi

echo "Done"

fi

rm -rf /tmp/buildstuff-${DIR}

#create the config_si.php file
DBNAME=`echo ${DIR} | sed 's/[-_]//g'`
echo "<?php
\$sugar_config_si = array (
'setup_db_host_name' => 'localhost',
'setup_db_database_name' => '${DBNAME}',
'setup_db_drop_tables' => 0,
'setup_db_create_database' => 1,
'demoData' => 'yes',
'setup_site_admin_user_name' => 'admin',
'setup_site_admin_password' => '1',
'setup_db_create_sugarsales_user' => 0,
'setup_db_admin_user_name' => 'root',
'setup_db_admin_password' => 'root',
'setup_db_sugarsales_user' => 'enguser',
'setup_db_sugarsales_password' => 'enguser',
'setup_db_type' => 'mysql',
'setup_license_key_users' => 10,
'setup_license_key_expire_date' => '2015-08-16',
'setup_license_key_oc_licences' => 10,
'setup_license_key' => 'sugartraining1',
'setup_site_url' => '${HOST_URL}/${DIR}/${FLAV}/',
'setup_system_name' => 'SugarCRM',
'default_currency_iso4217' => 'USD',
'default_currency_name' => 'US Dollars',
'default_currency_significant_digits' => '2',
'default_currency_symbol' => '$',
'default_date_format' => 'Y-m-d',
'default_time_format' => 'H:i',
'default_decimal_seperator' => '.',
'default_export_charset' => 'ISO-8859-1',
'default_language' => 'en_us',
'default_locale_name_format' => 's f l',
'default_number_grouping_seperator' => ',',
'export_delimiter' => ',',
);" >> ${BUILD_DIR}/${FLAV}/config_si.php

if [ -x `which growlnotify` ]
then
growlnotify -m "Build complete: ${VER} to Dir: ${BUILD_DIR}/${FLAV}" Builder
echo "Build complete: ${VER} to Dir: ${BUILD_DIR}/${FLAV}"
echo "Instance: ${HOST_URL}/${DIR}/${FLAV}/"
open "${HOST_URL}/${DIR}/${FLAV}/"
fi
else
# make sure it contains sugarcrm in the path to build it
if [[ $* == ${SUGAR_DIR}/sugarcrm/* ]]; then
php build.php --ver=${VER} --flav=${FLAV} --base_dir=${SUGAR_DIR}/sugarcrm --build_dir=${BUILD_DIR} --clean=0 --file=$*
elif [[ $* == ${SUGAR_DIR}/sidecar/* ]]; then
SIDECAR_BUILD_FILE=${*/${SUGAR_DIR}\/sidecar\//}
echo -n "Building SideCar File: ${SIDECAR_BUILD_FILE}..."
cp -r $* ${BUILD_DIR}/sidecar/${SIDECAR_BUILD_FILE}
echo "Done"
fi
fi