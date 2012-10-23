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
 * UpgradeRemoval62x.php
 * @author Collin Lee
 * This file contains the code to assit with removal of files during a 62x upgrade
 *
 */

require_once('modules/UpgradeWizard/UpgradeRemoval.php');

class UpgradeRemoval62x extends UpgradeRemoval
{
    /**
     * @var string minimal version for removal
     */
    public $version = '6.2.2';

/**
 * getFilesToRemove
 * Return an array of files/directories to remove for 62x upgrades
 * @param unknown_type $version
 */	
public function getFilesToRemove($version)
{
$files = array();

// In 6.2.2 we did the following
// 1) Removed include/JSON.js
// 2) Removed include/jsolait files
// 3) Upgraded more YUI 3 libraries
// 4) Upgraded TinyMCE from 2.x to 3.x version
// We will additionally clean up the legacy include/utils/external_cache direcotry

if (version_compare($version, $this->version, '<'))
{
	$files[] = 'include/utils/external_cache';
	$files[] = 'include/jsolait';
	$files[] = 'include/JSON.js';
        $files[] = 'include/javascript/tiny_mce/plugins/compat2x/editor_plugin.js';
	$files[] = 'include/javascript/tiny_mce/plugins/compat2x/editor_plugin_src.js';
        $files[] = 'include/javascript/tiny_mce/plugins/media/css/content.css';
	$files[] = 'include/javascript/tiny_mce/plugins/media/img';
        $files[] = 'include/javascript/yui3/build/cssgrids/grids-context-min.css';
	$files[] = 'include/javascript/yui3/build/cssgrids/grids-context.css';
	$files[] = 'include/javascript/yui3/build/get/get-min.js';
	$files[] = 'include/javascript/yui3/build/get/get.js';
	$files[] = 'include/javascript/yui3/build/node/node-aria-min.js';
	$files[] = 'include/javascript/yui3/build/node/node-aria.js';
	$files[] = 'include/javascript/yui3/build/widget/widget-position-ext-min.js';
	$files[] = 'include/javascript/yui3/build/widget/widget-position-ext.js';
	$files[] = 'include/javascript/yui3/build/yui-base/yui-base-min.js';
	$files[] = 'include/javascript/yui3/build/yui-base/yui-base.js';
	$files[] = 'include/javascript/yui/build/connection/connection_core-debug.js';
	$files[] = 'include/javascript/yui/build/datemath/datemath-debug.js';
	$files[] = 'include/javascript/yui/build/element-delegate/element-delegate-debug.js';
	$files[] = 'include/javascript/yui/build/event-delegate/event-delegate-debug.js';
	$files[] = 'include/javascript/yui/build/event-mouseenter/event-mouseenter-debug.js';
	$files[] = 'include/javascript/yui/build/event-simulate/event-simulate-debug.js';
	$files[] = 'include/javascript/yui/build/progressbar/progressbar-debug.js';
	$files[] = 'include/javascript/yui/build/storage/storage-debug.js';
	$files[] = 'include/javascript/yui/build/stylesheet/stylesheet-debug.js';
	$files[] = 'include/javascript/yui/build/swf/swf-debug.js';
	$files[] = 'include/javascript/yui/build/swfdetect/swfdetect-debug.js';
	$files[] = 'include/javascript/yui/build/swfstore/swf.js';
	$files[] = 'include/javascript/yui/build/swfstore/swfstore-debug.js';
	$files[] = 'jssource/src_files/include/jsolait';
	$files[] = 'modules/Activities/OpenListView.html';
	$files[] = 'modules/Activities/OpenListView.php';
}

if (version_compare($version, '6.2.4', '<'))
{
        $files[] = 'modules/Emails/EditView.html';
        $files[] = 'json.php';
}

return $files;	
}
	
		
}
