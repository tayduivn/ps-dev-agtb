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
<br>
{$mod.LBL_DESCRIPTION}
<br><br>
<form name='newsletterform' action='index.php' method='post'>
<input type='hidden' name='action' value='Save'>
<input type='hidden' name='module' value='Newsletters'>
<input type='hidden' name='subscribed' value=''>
<input type='hidden' name='unsubscribed' value=''>

<input type='submit' name=' {$app.LBL_SAVE_BUTTON_LABEL} ' value=' {$app.LBL_SAVE_BUTTON_LABEL} ' onclick='return submitForm()' class='button'>
&nbsp;&nbsp;&nbsp;
<input type='button' name=' {$mod.LBL_CHECKALL} ' value=' {$mod.LBL_CHECKALL} ' onclick='check_all(true); return false;' class='button'>
<input type='button' name=' {$mod.LBL_UNCHECKALL} ' value=' {$mod.LBL_UNCHECKALL} ' onclick='check_all(false); return false;' class='button'>
<table cellpadding='0' cellspacing='0' width='100%' border='0' class='listView'>
		{counter assign='rowsCounter'}
		{foreach name=rowIteration from=$newsletters.subscribed key=id item=rowData}
		{if $smarty.foreach.rowIteration.iteration is odd}
			{assign var='_bgColor' value=$bgColor[0]}
			{assign var='_rowColor' value=$rowColor[0]}
		{else}
			{assign var='_bgColor' value=$bgColor[1]}
			{assign var='_rowColor' value=$rowColor[1]}
		{/if}
		<tr height='20' onmouseover="setPointer(this, '{$id}', 'over', '{$_bgColor}', '{$bgHilite}', '');" onmouseout="setPointer(this, '{$id}', 'out', '{$_bgColor}', '{$bgHilite}', '');" onmousedown="setPointer(this, '{$id}', 'click', '{$_bgColor}', '{$bgHilite}', '');">
			<td width='1%' scope='row' align='left' valign=top class='{$_rowColor}S1' bgcolor='{$_bgColor}'>
				<input type='checkbox' value='prospect_list@{$rowData.prospect_list_id}@campaign@{$rowData.campaign_id}' name='newsletter{$rowsCounter}' checked>
			</td>
			<td scope='row' align='left' valign=top class='{$_rowColor}S1' bgcolor='{$_bgColor}'>
				<h3>{$rowData.name}</h3>
				{$rowData.description|nl2br}
			</td>
			<td width='10%' scope='row' align='left' valign=top class='{$_rowColor}S1' bgcolor='{$_bgColor}'>
				{$rowData.frequency}
			</td>
		</tr>
		{counter}
		{/foreach}
		{foreach name=rowIteration from=$newsletters.unsubscribed key=id item=rowData}
		{if $smarty.foreach.rowIteration.iteration is odd}
			{assign var='_bgColor' value=$bgColor[0]}
			{assign var='_rowColor' value=$rowColor[0]}
		{else}
			{assign var='_bgColor' value=$bgColor[1]}
			{assign var='_rowColor' value=$rowColor[1]}
		{/if}
		<tr height='20' onmouseover="setPointer(this, '{$id}', 'over', '{$_bgColor}', '{$bgHilite}', '');" onmouseout="setPointer(this, '{$id}', 'out', '{$_bgColor}', '{$bgHilite}', '');" onmousedown="setPointer(this, '{$id}', 'click', '{$_bgColor}', '{$bgHilite}', '');">
			<td width='1%' scope='row' align='left' valign=top class='{$_rowColor}S1' bgcolor='{$_bgColor}'>
				<input type='checkbox' value='prospect_list@{$rowData.prospect_list_id}@campaign@{$rowData.campaign_id}' name='newsletter{$rowsCounter}'>
			</td>
			<td scope='row' align='left' valign=top class='{$_rowColor}S1' bgcolor='{$_bgColor}'>
				<h2>{$rowData.name}</h2>
				{$rowData.description|nl2br}
			</td>
			<td width='10%' scope='row' align='left' valign=top class='{$_rowColor}S1' bgcolor='{$_bgColor}'>
				{$rowData.frequency}
			</td>
		</tr>
		{counter}
		{/foreach}
</table>

</form>

{literal}
<script>
function check_all(check) {
	for(var i = 1; i < {/literal}{$rowsCounter}{literal}; i++) {
		checkbox = eval('document.newsletterform.newsletter' + i);
		checkbox.checked = check;
	}
}
function submitForm() {
	var subscribed = Array();
	var unsubscribed = Array();

	for(var i = 1; i < {/literal}{$rowsCounter}{literal}; i++) {
		checkbox = eval('document.newsletterform.newsletter' + i);
		if(checkbox.checked)
			subscribed.push(checkbox.value);
		else
			unsubscribed.push(checkbox.value);
	}
	document.newsletterform.subscribed.value = subscribed.join(',');
	document.newsletterform.unsubscribed.value = unsubscribed.join(',');

	return true;
}
</script>
{/literal}

