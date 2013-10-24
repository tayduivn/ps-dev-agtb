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
 * Update .htaccess files or web.config files
 */
class SugarUpgradeUpgradeAccess extends UpgradeScript
{
    public $order = 3000;
    public $type = self::UPGRADE_CORE;

    public function run()
    {
        if(!version_compare($this->from_version, '6.6.0', '<')) {
            return;
        }
        require_once "install/install_utils.php";

        if(!empty($_SERVER['SERVER_SOFTWARE']) && strpos($_SERVER["SERVER_SOFTWARE"],'Microsoft-IIS') !== false) {
            $this->handleWebConfig();
        } else {
            $this->handleHtaccess();
        }
    }

    protected function handleWebConfig()
    {
        handleWebConfig();
    }

    protected function handleHtaccess()
    {
        if(!empty($_SERVER['SERVER_SOFTWARE'])) {
            $ignoreCase = (substr_count(strtolower($_SERVER['SERVER_SOFTWARE']), 'apache/2') > 0)?'(?i)':'';
        } else {
            $ignoreCase = false;
        }
        $htaccess_file = $this->context['source_dir']."/.htaccess";

        $status =  $this->putFile($htaccess_file, getHtaccessData($htaccess_file));
        if( !$status ){
            $this->fail(sprintf($this->mod_strings['ERROR_HT_NO_WRITE'], $htaccess_file));
            return;
        }

        if (empty($GLOBALS['sugar_config']['upload_dir'])) {
            $GLOBALS['sugar_config']['upload_dir']='upload/';
        }

        $uploadHta = "upload://.htaccess";

        $denyAll =<<<eoq
        	Order Deny,Allow
        	Deny from all
eoq;

        if(file_exists($uploadHta) && filesize($uploadHta)) {
        	// file exists, parse to make sure it is current
            $oldHtaccess = file_get_contents($uploadHta);
        	// use a different regex boundary b/c .htaccess uses the typicals
        	if(strstr($oldHtaccess, $denyAll) === false) {
                $oldHtaccess .= "\n";
        		$oldHtaccess .= $denyAll;
        	}
        	if(!file_put_contents($uploadHta, $oldHtaccess)) {
                $this->fail(sprintf($this->mod_strings['ERROR_HT_NO_WRITE'], $uploadHta));
        	}
        } else {
        	// no .htaccess yet, create a fill
        	if(!file_put_contents($uploadHta, $denyAll)) {
        		$this->fail(sprintf($this->mod_strings['ERROR_HT_NO_WRITE'], $uploadHta));
        	}
        }
    }
}
