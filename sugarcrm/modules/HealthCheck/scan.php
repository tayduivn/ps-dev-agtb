<?php

ob_clean();

require_once 'HealthCheck.php';

$healthCheck = new HealthCheck();

$result = $healthCheck->scan(
    array(
        '-v',
        '-l',
        'healthcheck.log',
        getcwd()
    )
);

$code = $healthCheck->getResultCode();

$response = array(
    'code' => $code,
    'data' => $result,
);

echo json_encode($response);

sugar_cleanup(true);

