<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
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
 * by SugarCRM are Copyright (C) 2004-2010 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
if(! is_admin($current_user)){
	sugar_die($GLOBALS['app_strings']['ERR_NOT_ADMIN']);
}

global $mod_strings, $sugar_config;
echo $mod_strings['LBL_REBUILD_SPRITES_DESC_SHORT']." <br /><br />";

include_once('include/SugarTheme/SugarSpriteBuilder.php');

$sb = new SugarSpriteBuilder();

// show output
$sb->silentRun = false;
//$sb->debug = true;

// add common image directories
$sb->addDirectory('default', 'include/images');
$sb->addDirectory('default', 'themes/default/images');
$sb->addDirectory('default', 'themes/default/images/SugarLogic');
$sb->addDirectory('default', 'custom/themes/default/images');

// add all theme image directories
if($dh = opendir('themes')) {
	while (($dir = readdir($dh)) !== false) {
		if ($dir != "." && $dir != ".." && is_dir('themes/'.$dir)) {
			$sb->addDirectory($dir, "themes/$dir/images");
		}
	}
	closedir($dh);
}

$sb->createSprites();

// build horizontal/vertical sprites
// TODO

echo "Done<br />";
?>
