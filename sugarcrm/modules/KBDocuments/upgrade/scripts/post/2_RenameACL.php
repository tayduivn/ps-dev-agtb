<?php
/**
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */

/**
 * Move old KBDocuments ACL to new one.
 */
class SugarUpgradeRenameACL extends UpgradeScript
{
    public $order = 2001;
    public $type = self::UPGRADE_CUSTOM;
    public $version = '7.5';

    public function run()
    {
        if (version_compare($this->from_version, '7.7.0', '<')) {
            $this->db->query("UPDATE acl_actions set category = 'KBContents' where category = 'KBDocuments'");
        }
    }
}
