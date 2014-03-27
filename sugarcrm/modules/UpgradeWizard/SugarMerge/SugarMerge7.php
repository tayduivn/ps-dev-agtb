<?php
 if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc.  All rights reserved.
 ********************************************************************************/

/**
 *
 * This is a hack needed because in 6.5 SugarMerge tried to load upgraders from new path
 * but new upgraders are not compatible with old code
 *
 */
class SugarMerge7 extends SugarMerge
{
    protected $upgrader;

    public function setUpgrader($u)
    {
        $this->upgrader = $u;
        if(!empty($u->fp)) {
            $this->setLogFilePointer($u->fp);
        }
    }

    public function getNewPath()
    {
        // HACK, see above
        return '';
    }

    /**
     * Override so that we would have better logging
     * @see SugarMerge::createHistoryLog()
     */
    protected function createHistoryLog($module,$customFile,$file)
    {
        $historyPath = 'custom/' . MB_HISTORYMETADATALOCATION . "/modules/$module/metadata/$file";
        $history = new History($historyPath);
        $timeStamp = $history->append($customFile);
        $this->log("Created history file after merge with new file: " . $historyPath .'_'.$timeStamp);
    }

    /**
     * Log a message
     * @param string $message
     */
    protected function log($message)
    {
        if($this->upgrader) {
            $this->upgrader->log($message);
        }
    }

}


