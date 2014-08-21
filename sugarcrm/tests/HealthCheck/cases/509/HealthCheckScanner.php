<?php

class S_509_HealthCheckScannerCasesTestWrapper extends HealthCheckScannerCasesTestWrapper
{
    public $md5_files = array(
        './modules/Accounts/Account.php' => 'fakeMD5'
    );
}
