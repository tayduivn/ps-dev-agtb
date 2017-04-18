#!/usr/bin/env bash

while :
do
    case "$1" in
        -u)
            INSTANCE_URL="$2"
            shift 2
            ;;
        -s)
            BEHAT_SUITE="$2"
            shift 2
            ;;
        *)
            break
            ;;
    esac
done

if [[ -z ${INSTANCE_URL} ]]
then
    printf "Please set up sugar url: -u http://sugar.url \n\n"
    exit
fi

if [[ -z ${BEHAT_SUITE} ]]
then
    printf "Please set up behat suite: -s saml \n\n"
    exit
fi

if [[ -z $TMPDIR ]]
then
TMPDIR="/tmp"
fi


SELENIUM_URL="https://goo.gl/uTXEJ1"
SELENIUM="selenium-server-standalone-3.3.1.jar"
SELENIUM_LOG="selenium.log"

if [ "$(uname)" == "Darwin" ]; then
    CHROME_DRIVER_URL="https://chromedriver.storage.googleapis.com/2.28/chromedriver_mac64.zip"
else
    CHROME_DRIVER_URL="https://chromedriver.storage.googleapis.com/2.28/chromedriver_linux64.zip"
fi
CHROME_DRIVER_PATH_ARCHIVE="chromedriver.zip"
CHROME_DRIVER="chromedriver"
CHROME_DRIVER_LOG="chromedriver.log"

INSTANCE_PATH=$PWD

cd $TMPDIR

if [ ! -f "$SELENIUM" ]
then
    printf "Downloading selenium...\n\n"
    wget $SELENIUM_URL -O $SELENIUM
fi

if [ ! -f "$CHROME_DRIVER" ]
then
    printf "Downloading chrome driver...\n\n"
    wget $CHROME_DRIVER_URL -O $CHROME_DRIVER_PATH_ARCHIVE
    unzip $CHROME_DRIVER_PATH_ARCHIVE        #-d cache/
fi

IS_CHROME_DRIVER_RUNNING=`ps -cax | grep $CHROME_DRIVER`
if [[  -z  $IS_CHROME_DRIVER_RUNNING ]]
then
    printf "Running chrome_driver \n\n"
    `./$CHROME_DRIVER >> $CHROME_DRIVER_LOG & `
    sleep 2
    printf "DONE \n\n"
fi

IS_SELENIUM_RUNNING=`ps -ax | grep "java -jar $SELENIUM" | grep -v grep| grep -v logs`
if [[  -z  $IS_SELENIUM_RUNNING  ]]
then
    printf "Running selenium \n\n"
    echo "java -jar $SELENIUM 2>> $SELENIUM_LOG &"
    java -jar $SELENIUM 2>> $SELENIUM_LOG &
    sleep 2
    printf "DONE \n\n"
fi

cd $INSTANCE_PATH

export BEHAT_PARAMS="{\"extensions\" : {\"Behat\\\\MinkExtension\" : {\"base_url\" : \"${INSTANCE_URL}\"}}}"

../../vendor/bin/behat -s ${BEHAT_SUITE}