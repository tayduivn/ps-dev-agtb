<?php

class S_502_HealthCheckScannerCasesTestWrapper extends HealthCheckScannerCasesTestWrapper
{
    public $md5_files = array(
        'randomFile.php' => 'incorrectMD5'
    );
}
