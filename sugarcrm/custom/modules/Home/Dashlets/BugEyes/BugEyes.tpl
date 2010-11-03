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

// $Id: JotPadDashlet.tpl,v 1.5 2006/08/23 00:13:44 awu Exp $

*}
<!-- core CSS --> 
<link rel="stylesheet" type="text/css" href="include/javascript/yui/assets/container.css"/>
	<link rel="stylesheet" type="text/css" href="include/javascript/yui/ext/resources/css/grid.css?1027"/>
	<link rel="stylesheet" type="text/css" href="include/javascript/yui/xext/assets/css/resizable.css?1030"/>
	<link rel="stylesheet" type="text/css" href="include/javascript/yui/ext/resources/css/toolbar.css"/>
	<link rel="stylesheet" type="text/css" href="include/javascript/yui/ext/resources/css/tabs.css?1030"/>
	<link rel="stylesheet" type="text/css" href="include/javascript/yui/assets/tabview/css/tabs.css">
<script src="include/javascript/yui/ext/yui-ext.js"></script>
<link rel="stylesheet" type="text/css" href="include/javascript/yui/assets/tabview/css/round_tabs.css">
<table>
	<tr><td><select id='bugEyesType'><option value='bug'>Bug</option><option value='case'>Case</option></select> #:<input id='bugEyesNumber' type='text' value='' size=4><input type='button' onclick='BugEyes.lookup(document.getElementById("bugEyesType").value, document.getElementById("bugEyesNumber").value,  "{$id}")' value='view' class='button'></td></tr>
	<tr><td>
	<div id='bugEyesTabs'>
	</div>
	</td></tr>

