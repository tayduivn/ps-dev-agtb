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

require_once 'modules/HealthCheck/Scanner/Scanner.php';

/**
 *
 * HealthCheck Scanner Web support
 *
 */
class ScannerWeb extends Scanner
{
    /**
     *
     * @var User
     */
    protected $currentUserBackup;

    /**
     *
     * Add additional init/cleanup because we run Healthcheck
     * inline with sugar code directly.
     *
     * @see Scanner::scan
     * @return array|void
     */
    public function scan() {
        $this->initWeb();
        $result = parent::scan();
        $this->cleanupWeb();
        return $result;
    }

    /**
     * Initialize before running scanner
     */
    protected function initWeb()
    {
        if (isset($GLOBALS['current_user'])) {
            $this->currentUserBackup = $GLOBALS['current_user'];
        }
    }

    /**
     * Cleanup after running scanner
     */
    protected function cleanupWeb()
    {
        if (!empty($this->currentUserBackup)) {
            $GLOBALS['current_user'] = $this->currentUserBackup;
        }
    }
}
