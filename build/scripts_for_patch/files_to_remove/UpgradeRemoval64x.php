<?php
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

/**
 * UpgradeRemoval64x.php
 * @author Collin Lee
 * This file contains the code to assit with removal of files during a 64x upgrade
 *
 */
require_once('modules/UpgradeWizard/UpgradeRemoval.php');

class UpgradeRemoval64x extends UpgradeRemoval
{
	
	
/**
 * getFilesToRemove
 * Return an array of files/directories to remove for 64x upgrades
 * @param unknown_type $version
 */	
public function getFilesToRemove($version)
{
$files = array();

// In 6.4.0 we did the following
// 1) Removed from include/javascript/yui3/assets dpSyntaxHighlighter.css and dpSyntaxHighlighter.js files

if($version < '640')
{
	$files[] = 'include/javascript/yui3/assets/dpSyntaxHighligther.css';
	$files[] = 'include/javascript/yui3/assets/dpSyntaxHighligther.js';
}

return $files;	
}
	
		
}

?>