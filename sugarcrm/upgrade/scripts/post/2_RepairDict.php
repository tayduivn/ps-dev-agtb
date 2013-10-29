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
 * There were some scenarios in 6.0.x whereby the files loaded in the extension tabledictionary.ext.php file
 * did not exist.  This would cause warnings to appear during the upgrade.  As a result, this
 * function scans the contents of tabledictionary.ext.php and then remove entries where the file does exist.
 */
class SugarUpgradeRepairDict extends UpgradeScript
{
    public $order = 2000;
    public $type = self::UPGRADE_CUSTOM;

    public function run()
    {
        $tableDictionaryExtDirs = array('custom/Extension/application/Ext/TableDictionary',
            'custom/application/Ext/TableDictionary');

        foreach ($tableDictionaryExtDirs as $tableDictionaryExt) {
            if (is_dir($tableDictionaryExt) && is_writable($tableDictionaryExt)) {
                $files = $this->findFiles($tableDictionaryExt);
                foreach($files as $file) {
                    $entry = $tableDictionaryExt . '/' . $file;
                    if (is_file($entry) && preg_match('/\.php$/i', $entry) && is_writeable($entry)) {
                        $fp = fopen($entry, 'r');

                        if ($fp) {
                            $altered = false;
                            $contents = '';

                            while ($line = fgets($fp)) {
                                if (preg_match('/\s*include\s*\(\s*[\'|\"](.*?)[\"|\']\s*\)\s*;/', $line, $match)) {
                                    if (!file_exists($match[1])) {
                                        $altered = true;
                                    } else {
                                        $contents .= $line;
                                    }
                                } else {
                                    $contents .= $line;
                                }
                            }

                            fclose($fp);
                        }

                        if ($altered) {
                            file_put_contents($entry, $contents);
                        }
                    } // if
                } // while
            } // if
        }
    }
}
