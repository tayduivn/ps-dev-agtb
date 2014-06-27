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

require_once __DIR__ . '/Scanner.php';

/**
 *
 * HealthCheck Scanner Web support
 *
 */
class ScannerWeb extends Scanner
{
    const VERDICT_FILE = 'healthcheck_verdict.log';

    /**
     * @see Scanner::scan
     * @return array|void
     */
    public function scan() {
        $result = parent::scan();
        $this->saveVerdict();
        return $result;
    }

    public function getLastVerdict()
    {
        return file_get_contents(static::VERDICT_FILE);
    }

    public function saveVerdict()
    {
        file_put_contents(static::VERDICT_FILE, $this->getStatus());
    }

    public function verifyLastVerdict()
    {
        $verdict = (string)$this->getLastVerdict();
        return $verdict != '' && $this->meta->getDefaultFlag($verdict) != ScannerMeta::FLAG_RED;
    }

}