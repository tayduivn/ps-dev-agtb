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


<script type="text/javascript" src="{sugar_getjspath file='include/javascript/sugar_grp_yui_widgets.js'}"></script>
{overlib_includes}
{$MODULE_TITLE}
<form enctype="multipart/form-data" real_id="extstep1" id="extstep1" name="extstep1" method="POST" action="index.php">
<input type="hidden" name="module" value="Import">
<input type="hidden" name="import_type" value="{$TYPE}">
<input type="hidden" name="source" value="{$smarty.request.external_source}">
<input type="hidden" name="action" value="ExtStep1">
<input type="hidden" name="import_module" value="{$IMPORT_MODULE}">
<input type="hidden" name="current_step" value="{$CURRENT_STEP}">
    
<div align="right">
    <span class="required" align="right">{$APP.LBL_REQUIRED_SYMBOL}</span> {$APP.NTC_REQUIRED}
</div>

<p>
{$MOD.LBL_SELECT_FIELDS_TO_MAP}
</p>
<br />

<table border="0" cellpadding="0" width="100%" id="importTable" class="detail view">
{foreach from=$rows key=key item=item name=rows}
{if $smarty.foreach.rows.first}
<tr>
    <td style="text-align: left;" scope="row">
        <b>{$MOD.LBL_EXTERNAL_FIELD}</b>&nbsp;
    </td>
    <td style="text-align: left;" scope="row">
        <b>{$MOD.LBL_DATABASE_FIELD}</b>&nbsp;
        {sugar_help text=$MOD.LBL_DATABASE_FIELD_HELP}
    </td>
    <td style="text-align: left;" scope="row" id="default_column_header">
        <a id="hide_default_link">{sugar_image image="advanced_search.gif" name="advanced_search" height="8" width="8" align="top"}</a>
        <span id="default_column_header_span"><b id="">{$MOD.LBL_DEFAULT_VALUE}</b>&nbsp;
        {sugar_help text=$MOD.LBL_DEFAULT_VALUE_HELP}</span>
    </td>
</tr>
{/if}
<tr>
    <td id="row_{$smarty.foreach.rows.index}_header">{$item.cell1}</td>
    <td valign="top" align="left" id="row_{$smarty.foreach.rows.index}_col_0">
        <select class='fixedwidth' name="colnum_{$smarty.foreach.rows.index}">
            <option value="-1">{$MOD.LBL_DONT_MAP}</option>
            {$item.field_choices}
        </select>
    </td>
    <td id="defaultvaluepicker_{$smarty.foreach.rows.index}" nowrap="nowrap">
        {$item.default_field}
    </td>
</tr>
{/foreach}
</table>
</form>

{$JAVASCRIPT}
{literal}
<script type="text/javascript" language="Javascript">
enableQS(false);
</script>
{/literal}