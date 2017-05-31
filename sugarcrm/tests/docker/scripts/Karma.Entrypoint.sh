#!/usr/bin/env bash
set -e

KARMA_CMD=$1

echo "Starting Xvfb..."
Xvfb "${DISPLAY}" -screen 0 "${GEOMETRY}" &

echo "Starting VNC Server..."
x11vnc -display "${DISPLAY}" -bg -nopw -xkb -usepw -shared -repeat -loop -forever > /usr/local/x11vnc.log 2>&1 &

# Run the command indicated in the first parameter
eval $KARMA_CMD
exit $?
