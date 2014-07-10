<?php

/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

require_once 'include/SugarSystemInfo/SugarSystemInfo.php';
require_once 'include/SugarHeartbeat/SugarHeartbeatClient.php';
require_once 'modules/HealthCheck/HealthCheckClient.php';

/**
 *
 * HealthCheck Controller
 *
 */
class HealthcheckController extends SugarController
{
    /**
     *
     * @see SugarController::$action_remap
     * @var array
     */
    protected $action_remap = array();

    /**
     *
     * Default action "index"
     */
    public function action_index()
    {
        $this->view = 'index';
    }

    /**
     *
     * Execute scan - returns json data
     */
    public function action_scan()
    {
        $this->view = 'ajax';

        // initialize scanner
        $scanner = $this->getWebScanner();
        $scanner->setInstanceDir(__DIR__ . '/../..');

        $hc = BeanFactory::getBean('HealthCheck')->run($scanner);
        if (!empty($hc->error)) {
            echo json_encode(array('error' => $hc->error));
        } else {
            // logmeta is already json encoded
            echo $hc->logmeta;
        }

        if($this->pingHeartbeat($hc)) {
            $GLOBALS['log']->info("HealthCheck: Heartbeat server has been pinged successfully.");
        } else {
            $GLOBALS['log']->error("HealthCheck: Unable to ping Heartbeat server.");
        }
    }

    protected function pingHeartbeat($hc)
    {
        $client = new SugarHeartbeatClient();

        $client->sugarPing();

        if (!$client->getError()) {
            $data = $this->getSystemInfo()->getInfo();
            $data['bucket'] = $hc->bucket;
            $data['flag'] = $hc->flag;
            $client->sugarHome($data['license_key'], $data);
            return $client->getError() == false;
        } else {
            $GLOBALS['log']->error("HealthCheck: " . $client->getError());
        }

        return false;
    }

    /**
     *
     * Export log file from last run
     */
    public function action_export()
    {
        $this->view = 'ajax';

        if ($hc = BeanFactory::getBean('HealthCheck')->getLastRun()) {
            $file = $hc->getLogFileName();
            if ($file && file_exists($file)) {
                $this->streamFileToBrowser($file);
            }
        }
        sugar_cleanup(true);
    }

    /**
     * Send health check log file to sugar
     */
    public function action_send()
    {
        $this->view = 'ajax';

        if ($hc = BeanFactory::getBean('HealthCheck')->getLastRun()) {
            $client = new HealthCheckClient();
            if ($client->send($this->getSystemInfo()->getLicenseKey(), $hc->getLogFileName())) {
                $GLOBALS['log']->info("HealthCheck: Logs have been successfully sent to HealthCheck server.");
                echo json_encode(array('status' => 'ok'));
                sugar_cleanup(true);
            }
        }
        $GLOBALS['log']->error("HealthCheck: Unable to send logs to HealthCheck server.");
        echo json_encode(array('status' => 'error'));
        sugar_cleanup(true);
    }

    /**
     *
     * Confirm action, will redirect to UpgradeWizard
     */
    public function action_confirm()
    {
        $this->view = 'ajax';
        $url = SugarConfig::getInstance()->get('site_url');
        $redirect = "{$url}/UpgradeWizard.php";
        if ($hc = BeanFactory::getBean('HealthCheck')->getLastRun()) {
            $redirect .= "?confirm_id={$hc->id}";
        }
        $this->set_redirect($redirect);
        $this->redirect();
    }

    /**
     *
     * Stream given file to browser
     * @param string $file Filename full path
     */
    protected function streamFileToBrowser($file)
    {
        header('Content-Type: application/text');
        header('Content-Disposition: attachment; filename='.basename($file));
        header('Content-Length: ' . filesize($file));
        ob_clean();
        flush();
        readfile($file);
    }

    protected function getSystemInfo()
    {
        return SugarSystemInfo::getInstance();
    }

    /**
     *
     * @return ScannerWeb
     */
    protected function getWebScanner()
    {
        require_once 'modules/HealthCheck/Scanner/ScannerWeb.php';
        return new ScannerWeb();
    }
}
