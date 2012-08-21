<?php
//FILE SUGARCRM flav=pro ONLY
 if(!defined('sugarEntry'))define('sugarEntry', true);
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/
//change directories to where this file is located.
//this is to make sure it can find dce_config.php
chdir(dirname(__FILE__));

require_once('include/entryPoint.php');
require_once('include/SugarSearchEngine/SugarSearchEngineAbstractBase.php');

if (SugarSearchEngineAbstractBase::isSearchEngineDown())
{
    sugar_die('Is fts server down?'."\n");
}

$sapi_type = php_sapi_name();
if (substr($sapi_type, 0, 3) != 'cli') {
    sugar_die("silentFTSIndex.php is CLI only.");
}

if(empty($current_language)) {
	$current_language = $sugar_config['default_language'];
}

$app_list_strings = return_app_list_strings_language($current_language);
$app_strings = return_application_language($current_language);

global $current_user;
$current_user = new User();
$current_user->getSystemUser();

$moules = ($argc > 1) ?  array($argv[1]) : array();
$clearData = ($argc == 2) ?  $argv[2] : TRUE;
require_once('include/SugarSearchEngine/SugarSearchEngineFullIndexer.php');
$indexer = new SugarSearchEngineFullIndexer();
$results = $indexer->performFullSystemIndex($moules, $clearData);