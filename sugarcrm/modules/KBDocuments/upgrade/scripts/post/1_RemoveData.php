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
 * Remove KBDocuments tables for clear install.
 */
class SugarUpgradeRemoveData extends UpgradeScript
{
    public $order = 1001;
    public $type = self::UPGRADE_CUSTOM;
    public $version = '7.5';

    public function run()
    {
        if (version_compare($this->from_version, '7.7.0', '<')) {
            // tables to delete
            $tables = array(
                'kbcontents',
                'kbcontents_cstm',
                'kbcontents_audit',
                'kbdocument_revisions',
                'kbdocuments',
                'kbdocuments_cstm',
                'kbdocuments_kbtags',
                'kbdocuments_views_ratings',
                'kbtags',
                'kbtags_cstm',
            );

            foreach ($tables as $table) {
                if ($this->db->tableExists($table)) {
                    foreach ($this->db->get_indices($table) as $index) {
                        if ($index['type'] == 'fulltext') {
                            $this->db->dropIndexes($table, array($index), true);
                        }
                    }
                    $this->db->dropTableName($table);
                }
            }
        }
    }
}
