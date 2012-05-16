#!/usr/bin/env bash
# Description: Used to create JUnit XML compatible CI reports 
# from running our Jasmine based test suite.
#
# E.g: ci_runner.sh -q -o ../../path/to/my_output_dir

# ----------- YOU NEED TO DEFINE THESE! ------------- #
PHANTOMJS="${HOME}/bin/phantomjs-1.5.0/bin/phantomjs"
TESTDIR="${HOME}/programming/sugar/Mango/sidecar/tests"
# --------------------------------------------------- #
RUNNER="global.html"
JASMINE_2_JUNITXML_RUNNER="phantomjs_jasminexml_runner.js"
QUIET=-1

function main() {
    prepare_script "$@"
    execute_jasmine_runner
}

function prepare_script() {
    parse_args "$@"
    get_full_path_to_output_dir ${OUTPUT_DIR}
    # we can get undefined results if we don't clean dir from previous runs
    check_if_output_dir_exists
}

function check_if_output_dir_exists() {
    if [ -d $ABS_OUTPUT_DIR ]; then
        if [ ${QUIET} -eq 1 ]; then
            rm -rf $ABS_OUTPUT_DIR
        else
            echo "$ABS_OUTPUT_DIR already exists. Would you like to remove?"
            read -p "Continue (y/n)? " CONT
            if [ "$CONT" == "y" ]; then
                rm -rf $ABS_OUTPUT_DIR
            else
                echo "Ok, please remove directory yourself and re-run."
                exit 1
            fi
        fi
    fi
}

function execute_jasmine_runner() {
    # Need to be in test directory
    pushd ${TESTDIR} > /dev/null 2>&1
    if [ ${QUIET} -eq 1 ]; then
        ${PHANTOMJS} ${TESTDIR}/runners/${JASMINE_2_JUNITXML_RUNNER} ${TESTDIR}/runners/${RUNNER} ${ABS_OUTPUT_DIR} > /dev/null 2>&1
    else
        echo "About to build JUnit XML Reports for Sidecar...this may take a minute"
        ${PHANTOMJS} runners/${JASMINE_2_JUNITXML_RUNNER} runners/${RUNNER} ${ABS_OUTPUT_DIR}
        echo
        echo "Wrote JUnit XML Reports to ${OUTPUT_DIR}"
        echo
        fails=`find ${ABS_OUTPUT_DIR} -type f -print0 | xargs -0 egrep "<failure>"`
        if [ -z "$fails" ]; then
            echo "Success!"
            echo
        else
            echo "Failure: "
            echo
            # preserves nice red failure color in my term ;=)
            find ${ABS_OUTPUT_DIR} -type f -print0 | xargs -0 egrep "<failure>"
            echo
        fi
    fi
    popd > /dev/null 2>&1
}

function usage() {
    echo "
Usage: $(basename $0) -o <output_dir> [-q quiet] 
       -o specifies the output directory 
       -q run in quiet mode 
"
    exit 1
}

function parse_args() {
    if [ $# -eq 0 ] ; then
        usage
    fi
    while getopts "qo:" opt; do
        case $opt in
            q) QUIET=1	;;
            o) OUTPUT_DIR="$OPTARG" ;;
        esac
    done
    shift $(($OPTIND - 1))

    if [ -z ${OUTPUT_DIR} ]; then
        usage
    fi
}

function get_full_path_to_output_dir() {
    local FILE=$1
    # remove any trailing slash
    FILE=${FILE%/}
    # Get the basename of the file
    local file_basename="${FILE##*/}"
    # extracts the directory component of the full path
    local DC="${FILE%$file_basename}"
    # cd to directory component and assign absolute full path
    if [ $DC ]; then  
        cd "$DC"
    fi
    local fileap=$(pwd -P)
    ABS_OUTPUT_DIR=$fileap/$file_basename

    cd "-" &>/dev/null
    return 0
}

main "$@"

exit 0

