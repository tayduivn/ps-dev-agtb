<?php
/*
* By installing or using this file, you are confirming on behalf of the entity
* subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
* the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
* http://www.sugarcrm.com/master-subscription-agreement
*
* If Company is not bound by the MSA, then by installing or using this file
* you are agreeing unconditionally that Company will be bound by the MSA and
* certifying that you have authority to bind Company accordingly.
*
* Copyright 2004-2014 SugarCRM Inc. All rights reserved.
*/
/**
* Class SugarUpgradeDropPDFHeaderLogo
*
* Drop header_logo_url from pdfmanager table
*/
class SugarUpgradeDropPDFHeaderLogo extends UpgradeScript
{
    public $order = 7450;
    public $type = self::UPGRADE_CUSTOM;
    public $version = '7.2';

    public function run()
    {
        if (!version_compare($this->from_version, '6.7.5', '==')) {
            // only need to run this upgrading from 6.7.5
            return;
        }

        // drop header_logo_url from pdfmanager table
        $sql = 'ALTER TABLE pdfmanager DROP COLUMN header_logo_url';
        if ($this->db->query($sql)) {
            $this->log('Removed header_logo_url from pdfmanager table');
        } else {
            $this->log('Failed to remove header_logo_url from pdfmanager table');
        }
    }
}

