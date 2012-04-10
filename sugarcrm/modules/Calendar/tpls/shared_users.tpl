{*
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
*}

<script language="javascript">
{if $edit_shared}
	{literal}
	SUGAR.util.doWhen(function(){
		return typeof cal_loaded != 'undefined' && cal_loaded == true && typeof dom_loaded != 'undefined' && dom_loaded == true;	
	},function(){
		CAL.toggle_shared_edit();
	});
	{/literal}
{/if}

{literal}
			function up(name){
				var td = document.getElementById(name+'_td');
				var obj = td.getElementsByTagName('select')[0];
				obj =(typeof obj == "string") ? document.getElementById(obj) : obj;
				if(obj.tagName.toLowerCase() != "select" && obj.length < 2)
					return false;
				var sel = new Array();
							
				for(i = 0; i < obj.length; i++){
					if(obj[i].selected == true) {
						sel[sel.length] = i;
					}
				}
				for(i in sel){
					if(sel[i] != 0 && !obj[sel[i]-1].selected) {
						var tmp = new Array(obj[sel[i]-1].text, obj[sel[i]-1].value);
						obj[sel[i]-1].text = obj[sel[i]].text;
						obj[sel[i]-1].value = obj[sel[i]].value;
						obj[sel[i]].text = tmp[0];
						obj[sel[i]].value = tmp[1];
						obj[sel[i]-1].selected = true;
						obj[sel[i]].selected = false;
					}
				}
			}			
			function down(name){
				var td = document.getElementById(name+'_td');
				var obj = td.getElementsByTagName('select')[0];
				if(obj.tagName.toLowerCase() != "select" && obj.length < 2)
					return false;
				var sel = new Array();
				for(i=obj.length-1; i>-1; i--){
					if(obj[i].selected == true) {
						sel[sel.length] = i;
					}
				}
				for(i in sel){
					if(sel[i] != obj.length-1 && !obj[sel[i]+1].selected) {
						var tmp = new Array(obj[sel[i]+1].text, obj[sel[i]+1].value);
						obj[sel[i]+1].text = obj[sel[i]].text;
						obj[sel[i]+1].value = obj[sel[i]].value;
						obj[sel[i]].text = tmp[0];
						obj[sel[i]].value = tmp[1];
						obj[sel[i]+1].selected = true;
						obj[sel[i]].selected = false;
					}
				}
			}
{/literal}
</script>

<div id="shared_cal_edit" style="display: none; width: 400px;">
<form name="shared_cal" action="index.php" method="post">
<div class="hd">{$MOD.LBL_EDIT_USERLIST}</div>
<div class="bd">	
	<input type="hidden" name="module" value="Calendar">
	<input type="hidden" name="action" value="index">
	<input type="hidden" name="edit_shared" value="">
	<input type="hidden" name="view" value="shared">
	
	{* //BEGIN SUGARCRM flav=pro ONLY *}
	<table cellpadding="1" cellspacing="1" border="0" class="edit view" align="center" width="100%">
		<tr>
			<td valign='top' nowrap><b>{$MOD.LBL_FILTER_BY_TEAM}</b></td>
			<td valign='top'>
				<select id="shared_team_id" onchange="this.form.edit_shared.value = '1'; this.form.submit();" name="shared_team_id">
					{$teams_options}
				</select>
		</tr>
	</table>
	{* //END SUGARCRM flav=pro ONLY *}
	
	<table cellpadding="0" cellspacing="3" border="0" align="center" width="100%">
		<tr><th valign="top" align="center" colspan="2">{$MOD.LBL_SELECT_USERS}</th></tr>
		<tr><td valign="top"></td><td valign="top">
			<table cellpadding="1" cellspacing="1" border="0" class="edit view" align="center">
				<tr>
					<td valign="top" nowrap=""><b>{$MOD.LBL_USERS}:</b></td>
					<td valign="top" id="shared_ids_td">
						<select id="shared_ids" name="shared_ids[]" multiple size="8">{$users_options}</select>
					</td>					
					<td>
						<a onclick="up('shared_ids');">{$UP}</a><br>
						<a onclick="down('shared_ids');">{$DOWN}</a>
					</td>
				</tr>
			</table>
		</td></tr>
	</table>
</div>
<div class="ft" style="text-align: right;">
	<input class="button" type="button" title="{$APP.LBL_SELECT_BUTTON_TITLE}" accesskey="{$APP.LBL_SELECT_BUTTON_KEY}" value="{$APP.LBL_SELECT_BUTTON_LABEL}" onclick="document.shared_cal.submit();"> 
	<input class="button" onclick="CAL.sharedDialog.cancel();" type="button" title="{$APP.LBL_CANCEL_BUTTON_TITLE}" accesskey="{$APP.LBL_CANCEL_BUTTON_KEY}" value="{$APP.LBL_CANCEL_BUTTON_LABEL}">
</div>
</form>
</div>
