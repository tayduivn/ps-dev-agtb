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
 * HealthCheck bean
 */
class HealthCheck extends Basic
{
    public $module_dir = 'HealthCheck';
    public $object_name = 'HealthCheck';
    public $table_name = 'healthcheck';

    // FIXME: add $beanList defs etc ...

    /**
     *
     * @var Scanner
     */
    protected $scanner;

    public function bean_implements($interface)
    {
        switch ($interface) {
            case 'ACL':
                return true;
        }
        return false;
    }

    /**
     * FIXME: non functional yet ...
     * @return HealthCheck
     */
    public function runHealthCheck()
    {
        $this->initScanner();
        $this->scanner->setInstanceDir(__DIR__ . '../..');
        $this->scanner->setLogFile("cache/xxxxx");

        $this->log_file = "cache/xxxx";
        $this->save();

        return $this;
    }

    protected function initScanner()
    {
        if (empty($this->scanner)) {
            $this->scanner = new Scanner();
        }
    }
}
