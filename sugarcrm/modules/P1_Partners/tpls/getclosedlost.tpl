{*

/**
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

 */

*}
<table border="0" cellpadding="0" cellspacing="0" width="100%">
<tbody>
<tr>
<td class="dataLabel" width="10.5%" valign="top">{$MOD_OPPORTUNITIES.LBL_CLOSED_LOST_REASON_C}: </td>
<td class="dataField" width="37%" style="padding-left:23px;">
{html_options id="closed_lost_reason_c" name="Opportunitiesclosed_lost_reason_c" options=$opp_fields.closed_lost_reason_c.options selected=$opp_fields.closed_lost_reason_c.value OnChange="checkOppClosedReasonDependentDropdown('closed_lost_reason_detail_c',false,'','Opportunities')"}
</td></tr>
<tr>
<td class="dataLabel" width="10.5%" valign="top">{$MOD_OPPORTUNITIES.LBL_CLOSED_LOST_REASON_DETAIL}: </td>
<td class="dataField" width="37%" style="padding-left:23px;">
<span id=\'closedDetailsParentSpan\'>
	<select name="Opportunitiesclosed_lost_reason_detail_c" id="closed_lost_reason_detail_c" title=''></select>
</span>
</td>
</tr>
<tr>
<td class="dataLabel" width="10.5%" valign="top">{$MOD_OPPORTUNITIES.LBL_PRIMARY_REASON_COMPETITOR}:</td>
<td class="dataField" width="37%" style="padding-left:23px;">{html_options id="primary_reason_competitor_c" name="Opportunitiesprimary_reason_competitor_c" options=$opp_fields.primary_reason_competitor_c.options selected=$opp_fields.primary_reason_competitor_c.value}</td>
</tr>
<tr>
<td class="dataLabel" width="10.5%" valign="top">{$MOD_OPPORTUNITIES.LBL_CLOSED_LOST_DESCRIPTION}: <span class="required">*</span></td>
<td class="dataField" width="37%" style="padding-left:23px;"><textarea name="Opportunitiesclosed_lost_description" id="closed_lost_description" cols="50" rows="2">{$opp_fields.closed_lost_description.value}</textarea></td>
</tr>
</tbody>
</table>
