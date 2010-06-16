{*
/**
 * LICENSE: The contents of this file are subject to the SugarCRM Professional
 * End User License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You
 * may not use this file except in compliance with the License.  Under the
 * terms of the license, You shall not, among other things: 1) sublicense,
 * resell, rent, lease, redistribute, assign or otherwise transfer Your
 * rights to the Software, and 2) use the Software for timesharing or service
 * bureau purposes such as hosting the Software for commercial gain and/or for
 * the benefit of a third party.  Use of the Software may be subject to
 * applicable fees and any use of the Software without first paying applicable
 * fees is strictly prohibited.  You do not have the right to remove SugarCRM
 * copyrights from the source code or user interface.
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
 * by SugarCRM are Copyright (C) 2006 SugarCRM, Inc.; All Rights Reserved.
 */
*}
<html>
<head>
{$scripts}
{literal}
	<link rel="stylesheet" type="text/css" href="include/javascript/yui/assets/container.css"/>
	<link rel="stylesheet" type="text/css" href="include/javascript/yui/ext/resources/css/grid.css?1027"/>
	<link rel="stylesheet" type="text/css" href="include/javascript/yui/ext/resources/css/toolbar.css"/>
	<link rel="stylesheet" type="text/css" href="include/javascript/yui/ext/resources/css/tabs.css?1030"/>
	<link rel="stylesheet" type="text/css" href="include/javascript/yui/assets/tabview/css/tabs.css">
<link rel="stylesheet" type="text/css" href="include/javascript/yui/assets/tabview/css/round_tabs.css">
<style type="text/css">
#demo { width:100%; }
#demo .yui-content {
    padding:1em; /* pad content container */
}
.list {list-style:square;width:500px;padding-left:16px;}
.list li{padding:2px;font-size:8pt;}

/* hide the tab content while loading */
.tab-content{display:none;}

pre {
   font-size:11px;
}

#tabs1 {width:100%;}
#tabs1 .yui-ext-tabbody {border:1px solid #999;border-top:none;}
#tabs1 .yui-ext-tabitembody {display:none;padding:10px;}

/* default loading indicator for ajax calls */
.loading-indicator {
	font-size:8pt;
	background-image:url('../../resources/images/grid/loading.gif');
	background-repeat: no-repeat;
	background-position: left;
	padding-left:20px;
}
</style>
{/literal}
</head>
<body>
<div id='syncPortal'>
<p>{$MOD.LBL_SECURITY_WARNING}</p>
<table>
	<tr>
		<td>{$MOD.LBL_USER_NAME}</td>
		<td><input type='text' id='portal_user_name'></td>
	</tr>
	<tr>
		<td>{$MOD.LBL_PASSWORD}</td>
		<td><input type='password' id='portal_password'></td>
	</tr>
	<tr>
		<td></td>
		<td><input type='button' id='portal_sync_begin' name='portal_sync_begin' value='{$MOD.LBL_BEGIN_SYNC}' class='button' onClick='PortalSync.login();'></td>
	</tr>
</table>
</div>
</body>
</html>