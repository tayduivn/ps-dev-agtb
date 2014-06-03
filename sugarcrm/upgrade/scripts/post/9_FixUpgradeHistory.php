<?php
 if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/**
 * Fix upgrade_history.manifest:
 *
 * The format of this column used to be a base64 encoded serialized php array.
 * Since the new CLI upgrader the content has been json encoded. This script
 * fixes the formatting and re-encodes in the old way if json is detected.
 */
class SugarUpgradeFixUpgradeHistory extends UpgradeScript
{
    public $order = 9901;
    public $type = self::UPGRADE_DB;

    /**
     *
     * Execute upgrade tasks
     * @see UpgradeScript::run()
     */
    public function run()
    {
        $q = $this->db->query("SELECT id, manifest FROM upgrade_history");
        while ($row = $this->db->fetchByAssoc($q, false)) {
            if ($this->isJson($row['manifest'])) {
                $update = sprintf(
                    "UPDATE upgrade_history SET manifest = %s WHERE id = %s",
                    $this->db->quoted($this->reEncode($row['manifest'])),
                    $this->db->quoted($row['id'])
                );
                $this->db->query($update);
            }
        }
    }

    /**
     *
     * Check if passed in string is json encoded
     * @param string $string
     * @return boolean
     */
    protected function isJson($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    /**
     *
     * Re-encode given string using base64/serialize
     * @param string $string
     * @return string
     */
    protected function reEncode($string)
    {
        return base64_encode(serialize(json_decode($string)));
    }
}
