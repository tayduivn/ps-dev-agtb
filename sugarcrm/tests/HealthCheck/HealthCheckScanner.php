<?php

require_once 'modules/HealthCheck/Scanner/Scanner.php';

class HealthCheckScannerCasesTestWrapper extends HealthCheckScanner
{
    public $md5_files = array();
    public $bwcModulesHash = array();
    private $logString = '';

    /**
     * Initialize instance environment
     * @return bool False means this instance is messed up
     */
    protected function init()
    {
        $this->db = DBManagerFactory::getInstance();
        $this->bwcModulesHash = array_flip($this->bwcModules);
        return true;
    }

    protected function log($message, $tag = 'INFO')
    {
        // nothing to do
    }

    public function getVersionAndFlavor()
    {
        return array('6.5.0', 'ent');
    }

    public function ping()
    {
        // nothing to do
    }

    public function tearDown()
    {
        // nothing to do
    }
}
