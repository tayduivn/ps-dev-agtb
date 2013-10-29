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
 * Run SQL scripts from $temp_dir/scripts/ relevant to current conversion, e.g.
 * scripts/65x_to_67x_mysql.sql
 */
class SugarUpgradeRunSQL extends UpgradeScript
{
    public $order = 1000;
    public $type = self::UPGRADE_DB;

    public function run()
    {
        $vfrom = $this->implodeVersion($this->from_version, 2);
        $vto = $this->implodeVersion($this->to_version, 2);
        $this->log("Looking for SQL scripts from $vfrom/{$this->from_flavor} to $vto/{$this->to_flavor}");
        if ($vfrom == $vto) {
            if ($this->from_flavor == $this->to_flavor) {
                // minor upgrade, no schema changes
                return;
            } else {
                $script = "{$vfrom}_{$this->from_flavor}_to_{$this->to_flavor}";
            }
        } else {
            $script = "{$vfrom}_to_{$vto}";
        }
        $script .= "_" . $this->db->getScriptName() . ".sql";
        $filename = $this->context['temp_dir'] . "/scripts/$script";
        $this->log("Script name: $script ($filename)");
        if (file_exists($filename)) {
            $this->parseAndExecuteSqlFile($filename);
        }
    }

    protected function parseAndExecuteSqlFile($sqlScript)
    {
        // TODO: resume support?
        $contents = file($sqlScript);
        $anyScriptChanges = $contents;
        $resumeAfterFound = false;
        foreach($contents as $line) {
            if (strpos($line, '--') === false) {
               $completeLine .= " " . trim($line);
               if (strpos($line, ';') !== false) {
                   $query = str_replace(';', '', $completeLine);
                   if ($query != null) {
                       $this->db->query($query);
                   }
                   $completeLine = '';
                }
            }
        } // foreach
    }
}
