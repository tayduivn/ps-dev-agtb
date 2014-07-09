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
 * HealthCheck
 */
class HealthCheck extends Basic
{
    const CACHE_DIR = 'healthcheck';

    public $module_dir = 'HealthCheck';
    public $object_name = 'HealthCheck';
    public $table_name = 'healthcheck';

    /**
     *
     * Perform healthcheck
     * @param Scanner $scanner
     * @return HealthCheck
     */
    public static function runHealthCheck(Scanner $scanner)
    {
        $hc = BeanFactory::getBean('HealthCheck');

        // log file setup
        $cacheDir = sugar_cached(self::CACHE_DIR);
        SugarAutoLoader::ensureDir($cacheDir);
        $hc->logfile = 'healthcheck-' . time() . '.log';
        $scanner->setLogFile($cacheDir . "/" .$hc->logfile);

        try {
            $logMeta = $scanner->scan();
            $hc->logmeta = json_encode($logMeta);
            $hc->bucket = $scanner->getStatus();
            $hc->flag = $scanner->getFlag();

        } catch (Exception $e) {
            $GLOBALS['log']->fatal("Error executing Health Check: " . $e->getMessage());
            $hc->error = $e->getMessage();
        }

        $hc->save();
        return $hc;
    }

    /**
     *
     * Get most recent healtcheck run
     * @return HealthCheck
     */
    public static function getLastRun()
    {
        $sql = "SELECT id FROM healthcheck WHERE deleted = 0 ORDER BY date_entered DESC";
        $id = DBManagerFactory::getInstance()->getOne($sql, false, 'Error fetching most recent healtcheck record');
        if ($id) {
            return BeanFactory::getBean('HealthCheck', $id);
        }
    }

    /**
     *
     * Return full path for log file
     */
    public function getLogFileName()
    {
        if (!empty($this->logfile)) {
            return sugar_cached(self::CACHE_DIR) . "/" . $this->logfile;
        }
    }

    /**
     *
     * @see Basic::get_summary_text()
     */
    public function get_summary_text()
    {
        return '';
    }
}
