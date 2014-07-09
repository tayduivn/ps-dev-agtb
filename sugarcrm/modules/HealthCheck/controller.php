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

        $hc = HealthCheck::runHealthCheck($scanner);
        if (!empty($hc->error)) {
            echo json_encode(array('error' => $hc->error));
        } else {
            // logmeta is already json encoded
            echo $hc->logmeta;
        }

        // TODO - add heartbeat send including bucket ...
        // Also verify that Scanner::ping() is fully removed from there ...
    }

    /**
     *
     * Export log file from last run
     */
    public function action_export()
    {
        $this->view = 'ajax';

        if ($hc = HealthCheck::getLastRun()) {
            $file = $hc->getLogFileName();
            if ($file && file_exists($file)) {
                $this->streamFileToBrowser($file);
            }
        }
        sugar_cleanup(true);
    }

    /**
     *
     * Send health check log file to sugar
     */
    public function action_send()
    {
        $this->view = 'ajax';

        if ($hc = HealthCheck::getLastRun()) {
            // TODO - return json with success or error
        }
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
        if ($hc = HealthCheck::getLastRun()) {
            $redirect .= "?confirm_id={$hc->id}";
        }
        header("Location: {$redirect}");
        exit();
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
