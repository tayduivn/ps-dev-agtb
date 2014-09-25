<?php

require_once 'include/SugarSystemInfo/SugarSystemInfo.php';
require_once 'include/SugarHeartbeat/SugarHeartbeatClient.php';
require_once 'modules/HealthCheck/HealthCheckClient.php';

class HealthCheckHelper
{
    protected static $instance;

    /**
     * Private constructor
     */
    private function __construct()
    {
    }

    /**
     * @return HealthCheckHelper
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * @var array
     */
    protected $registry = array(
        'web' => array(
            'modules/HealthCheck/Scanner/ScannerWeb.php',
            'HealthCheckScannerWeb'
        ),
        'cli' => array(
            'modules/HealthCheck/Scanner/ScannerCli.php',
            'HealthCheckScannerCli'
        )
    );

    /**
     * @return HealthCheckScannerWeb
     */
    public function getScanner($type)
    {
        if (isset($this->registry[$type])) {
            list($file, $class) = $this->registry[$type]['file'];
            require_once $file;
            return new $class();
        }

        return null;
    }

    /**
     * Notifies heartbeat server about the fact that heath check has been run.
     * Sends the licence key, the bucket and and the flag
     *
     * @param array $data
     * @return bool
     */
    public function pingHeartbeat($data)
    {
        $client = new SugarHeartbeatClient();
        $client->sugarPing();

        if (!$client->getError()) {
            $data = array_merge($this->getSystemInfo()->getInfo(), $data);
            $client->sugarHome($this->getSystemInfo()->getLicenseKey(), $data);
            return $client->getError() == false;
        } else {
            $GLOBALS['log']->error("HealthCheck: " . $client->getError());
        }
        return false;
    }

    /**
     * Send health check log file to sugar
     * @param string $file
     * @return bool
     */
    public function sendLog($file)
    {
        $client = new HealthCheckClient();
        if ($client->send($this->getSystemInfo()->getLicenseKey(), $file)) {
            $GLOBALS['log']->info("HealthCheck: Logs have been successfully sent to HealthCheck server.");
            return true;
        }
        $GLOBALS['log']->error("HealthCheck: Unable to send logs to HealthCheck server.");
        return false;
    }

    /**
     * @return SugarSystemInfo
     */
    protected function getSystemInfo()
    {
        return SugarSystemInfo::getInstance();
    }


}