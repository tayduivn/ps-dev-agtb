#!/bin/bash

for item in Xvfb chrome firefox x11vnc; do
    pkill -9 $item || true
done
