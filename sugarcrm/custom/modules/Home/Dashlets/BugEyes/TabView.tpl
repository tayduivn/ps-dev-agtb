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
<table>
<tr>
	<td width='15%' nowrap colspan='4'><a href='javascript:void(0);' class='tabDetailViewDFLink' onclick='BugEyes.removeTab("{$type}{$bug->number}")' >{$label.close}</a>&nbsp;{if !empty($bug->id)}<a href='index.php?module={$fctype}s&action=DetailView&record={$bug->id}' class='tabDetailViewDFLink' >{$label.view}</a>&nbsp;<a href='index.php?module={$fctype}s&action=EditView&record={$bug->id}'  class='tabDetailViewDFLink'>{$label.edit}</a> {/if}</td>
</tr>
{if !empty($bug->id)}
<tr>
	<td width='15%' nowrap>{$fctype} #</td><td width='35%'>{$bug->number}</td>
	<td width='15%' nowrap>{$label.priority}</td><td width='35%'>{$bug->priority}</td>
</tr>
<tr>
	<td nowrap>{$label.name}</td><td >{$bug->name}</td>
	<td nowrap>{$label.status}</td><td>{$bug->status}</td>
</tr>
<tr>
	<td nowrap>{$label.assigned_user_name}</td><td>{$bug->assigned_user_name}</td>
	<td nowrap>{$APP.LBL_TEAM}</td><td>{$bug->team_name}</td>
</tr>
<tr>
	<td nowrap>{$label.fixed_in_release}</td><td>{$bug->fixed_in_release}</td>
</tr>
<tr>
	<td nowrap>{$label.description}</td>
	<td colspan='3'><textarea readonly cols=80>{$bug->description}</textarea></td>
</tr>
<tr>
	<td nowrap>{$label.work_log}</td>
	<td colspan='3'><textarea readonly cols=80>{$bug->log}</textarea></td>
</tr>
{else}
<table width='100%'>
<tr><td>
{$label.not_found}
</td></tr>
</table>
{/if}

</table>


