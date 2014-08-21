<?php

class S_512_HealthCheckScannerCasesTestWrapper extends HealthCheckScannerCasesTestWrapper
{
    public $md5_files = array(
        './modules/Accounts/Account.php' => 'fakeMD5'
    );
}
