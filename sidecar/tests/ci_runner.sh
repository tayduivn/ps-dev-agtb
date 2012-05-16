#!/usr/bin/env bash
# Description: Used to kick off the JUnit XML compatible reports 
# for results of running our Jasmine based test suite.
#
# E.g: ci_runner.sh -q -o ../path/to/my_output_dir

# ----------- YOU NEED TO DEFINE THIS! ------------- #
PHANTOMJS="${HOME}/bin/phantomjs-1.5.0/bin/phantomjs"

RUNNER="global.html"
JASMINE_2_JUNITXML_RUNNER="phantomjs_jasminexml_runner.js"
QUIET=-1

function main() {
    # we can get undefined results if we don't clean dir from previuos run
    check_output_directory
    execute_jasmine_runner
}

function check_output_directory() {
    if [ -d $OUTPUT_DIR ]; then
        echo "$OUTPUT_DIR already exists. Would you like to remove?"
        read -p "Continue (y/n)? " CONT
        if [ "$CONT" == "y" ]; then
            rm -rf $OUTPUT_DIR
        else
            echo "Ok, please remove directory yourself and re-run."
            exit 1
        fi
    fi
}

function execute_jasmine_runner() {
    if [ ${QUIET} -eq 1 ]; then
        ${PHANTOMJS} runners/${JASMINE_2_JUNITXML_RUNNER} runners/${RUNNER} ${OUTPUT_DIR} > /dev/null 2>&1
    else
        echo "About to build JUnit XML Reports for Sidecar...this may take a minute"
        ${PHANTOMJS} runners/${JASMINE_2_JUNITXML_RUNNER} runners/${RUNNER} ${OUTPUT_DIR}
        echo
        echo "Wrote JUnit XML Reports to ${OUTPUT_DIR}"
        echo
        fails=`find ${OUTPUT_DIR} -type f -print0 | xargs -0 egrep "<failure>"`
        if [ -z "$fails" ]; then
            echo "Success!"
            echo
        else
            echo "Failure: "
            echo
            # preserves nice red failure color in my term ;=)
            find ${OUTPUT_DIR} -type f -print0 | xargs -0 egrep "<failure>"
            echo
        fi
    fi
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

parse_args "$@"
main
exit 0

