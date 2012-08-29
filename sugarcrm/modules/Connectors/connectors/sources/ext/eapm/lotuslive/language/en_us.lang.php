<?php
//FILE SUGARCRM flav=pro ONLY
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

$connector_strings = array (
    'LBL_LICENSING_INFO' => '<table border="0" cellspacing="1"><tr><td valign="top" width="35%" class="dataLabel">Obtain a Key and Secret from IBM SmartCloud&copy; by registering your Sugar instance as a new application. The ability to register will be possible starting May 8, 2011.<br>
&nbsp;<br>
Steps to register your instance:<br>
&nbsp;<br>
<ol>
<li>Log in to your IBM SmartCloud account (you must be an administrator)</li>
<li>Go to Administration -> Manage Organization</li>
<li>Go to the "Integrated Third-Party Apps" link on the sidebar and enable SugarCRM for all users.</li>
<li>Go to "Internal Apps" on the sidebar and "Register App"</li>
<li>Name this app whatever you want (say "SugarCRM Production"), and be sure _NOT_ to check the OAuth 2.x checkbox at the bottom of the pop up window.</li>
<li>After the app has been created, click on the little triangle thing to the right of the app name and select "Show Credentials" from the dropdown menu.</li>
<li>Copy the credentials below.</li>
</ol>
</td></tr></table>',
    'oauth_consumer_key' => 'OAuth Consumer Key',
    'oauth_consumer_secret' => 'OAuth Consumer Secret',
);

