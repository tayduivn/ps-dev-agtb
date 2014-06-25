<?php

ob_clean();

header('Content-Disposition: attachment; filename="healthcheck.log"');

if (is_readable("healthcheck.log")) {
    echo file_get_contents('healthcheck.log');
}

sugar_cleanup(true);