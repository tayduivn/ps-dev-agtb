<!--
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement ("License") which can be viewed at
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
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
-->
<!--//FILE SUGARCRM flav=int ONLY -->
{literal}
<style>
	ol {
		padding-left:10px;
		margin-bottom:10px;
	}
</style>
{/literal}
<div style="padding:5px;">
<b>TO-DOs</b> <i><a href="javascript:refreshTodos();">refresh</a></i>
<br><br>
<table cellpadding="4" cellspacing="2" border="0">
	<tr>
		<td valign="top">
			<b>High Priority</b>
			<br>
			<ol>
				<li class="error">Change All Usage of MsgNo to UID as MsgNo changes on deletion</li>
				<li class="error">Context Menus - DetailViews</li>
				<li class="error">Check Mail reconciliation</li>
				<li class="error">My Email Unread decrementer</li>
			</ol>
		</td>
	</tr>
	<tr>
		<td valign="top">
			<b>Support Demo Reqs</b>
			<br>
			<ol>
				<li class="error">Smart Folders</li>
			</ol>
		</td>
	</tr>
	<tr>
		<td valign="top">
			<b>General</b>
			<br>
			<ol>
				<li class="error">ANDY-Cascade pane resize when collapsing Sugar side menu</li>
				<li class="error">Collapsible Preview Pane</li>
			</ol>
		</td>
	</tr>
	<tr>
		<td valign="top">
			<b>Back End</b>
			<br>
			<ol>
			</ol>
		</td>
	</tr>
	<tr>
		<td valign="top">
			<b>Address Book</b>
			<br>
			<ol style="list-style-type: upper-alpha"  class="">
				<li class="error">Save & Add to Address Book</li>
				<li class="error">Save Contact Edit</li>
				<li class="error">Contact List Paging</li>
			</ol>
		</td>
	</tr>
	<tr>
		<td valign="top">
			<b>Compose</b>
			<br>
			<ol style="list-style-type: upper-alpha"  class="">
				<li class="error">Reply/Forward from Imported Email</li>
				<li class="error">Save/Retrieve Draft code</li>
				<li class="error">ANDY-Confirm close</li>
			</ol>
		</td>
	</tr>
	<tr>
		<td valign="top">
			<b>Context Menus</b>
			<br>
			<ol style="list-style-type: upper-alpha"  class="">
			</ol>
		</td>
	</tr>
	<tr>
		<td valign="top">
			<b>DetailView</b>
			<br>
			<ol style="list-style-type: upper-alpha"  class="">
				<li class="error">Quick-link code</li>
				<li class="error">Hide/show images inline in emails</li>
			</ol>
		</td>
	</tr>
	<tr>
		<td valign="top">
			<b>Folders</b>
			<br>
			<ol style="list-style-type: upper-alpha"  class="">
				<li class="error">Folder Rename code in context menus</li>
			</ol>
		</td>
	</tr>
	<tr>
		<td valign="top">
			<b>ListView</b>
			<br>
			<ol style="list-style-type: upper-alpha"  class="">
				<li class="error">Save State on Sort Order per folder</li>
				<li class="error">ANDY-Show name only, show addy if not found</li>
				<li class="error">ANDY-ListView bar does not show unread count</li>
			</ol>
		</td>
	</tr>
	<tr>
		<td valign="top">
			<b>Rules Wizard</b>
			<br>
			<ol style="list-style-type: upper-alpha"  class="">
				<li class="error">Reorder Rule Priority in UI</li>
			</ol>
		</td>
	</tr>
	<tr>
		<td valign="top">
			<b>Search</b>
			<br>
			<ol style="list-style-type: upper-alpha"  class="">
				<li class="error">Advanced Search - Local/Server, etc.</li>
				<li class="error">ANDY-Simple Search "Enter" submits form</li>
			</ol>
		</td>
	</tr>
	<tr>
		<td valign="top">
			<b>Settings</b>
			<br>
			<ol style="list-style-type: upper-alpha"  class="">
				<li class="error">SMTP in Account</li>
				<li class="error">Signatures in Accounts</li>
			</ol>
		</td>
	</tr>
	<tr>
		<td valign="top">
			<b>Known Issues</b>
			<br>
			<ol style="list-style-type: upper-alpha"  class="">
				<li class="error">Mac FF 2.0.0.3 Handles Keypress event differently than other FFs</li>
			</ol>
		</td>
	</tr>
	<tr>
		<td valign="top">
			<b>Milestone V (post release)</b>
			<br>
			<ol style="list-style-type: upper-alpha"  class="">
				<li class="error">Activity Alert in Status Bar</li>
				<li class="error">iCal Import</li>
				<li class="error">ANDY-context menu on email address to perform Contacts lookup</li>
			</ol>
		</td>
	</tr>
	<tr>
		<td valign="top">
			<b>Clean-up & Refactor</b>
			<br>
			<ol style="list-style-type: upper-alpha"  class="">
				<li class="error">Unread Count Controller</li>
				<li class="error">c0_composeNewEmail() & composeEmailPrep() code overlap</li>
			</ol>
		</td>
	</tr>
</table>
</div>
