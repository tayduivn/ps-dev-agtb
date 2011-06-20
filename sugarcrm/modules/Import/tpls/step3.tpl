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

// $Id: step3.tpl 25541 2007-01-11 21:57:54Z jmertic $

*}
{literal}
<style>
<!--
textarea { width: 20em }
-->
</style>
{/literal}
<script type="text/javascript" src="{sugar_getjspath file='include/javascript/sugar_grp_yui_widgets.js'}"></script>
{overlib_includes}
{$MODULE_TITLE}
<form enctype="multipart/form-data" real_id="importstep3" id="importstep3" name="importstep3" method="POST" action="index.php">
<input type="hidden" name="module" value="Import">
<input type="hidden" name="custom_delimiter" value="{$CUSTOM_DELIMITER}">
<input type="hidden" name="custom_enclosure" value="{$CUSTOM_ENCLOSURE}">
<input type="hidden" name="import_type" value="{$TYPE}">
<input type="hidden" name="source" value="{$SOURCE}">
<input type="hidden" name="source_id" value="{$SOURCE_ID}">
<input type="hidden" name="action" value="Step3">
<input type="hidden" name="import_module" value="{$IMPORT_MODULE}">
<input type="hidden" name="has_header" value="{$HAS_HEADER}">
<input type="hidden" name="tmp_file" value="{$TMP_FILE}">
<input type="hidden" name="tmp_file_base" value="{$TMP_FILE}">
<input type="hidden" name="firstrow" value="{$FIRSTROW}">
<input type="hidden" name="columncount" value ="{$COLUMNCOUNT}">
<input type="hidden" name="current_step" value="{$CURRENT_STEP}">
<input type="hidden" name="importlocale_charset" value="{$smarty.request.importlocale_charset}">

<input type="hidden" name="display_tabs_def">

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
    {if $HAS_HEADER == 'on'}
    <td style="text-align: left;" scope="row">
        <b>{$MOD.LBL_HEADER_ROW}</b>&nbsp;
        {sugar_help text=$MOD.LBL_HEADER_ROW_HELP}
    </td>
    {/if}
    <td style="text-align: left;" scope="row">
        <b>{$MOD.LBL_DATABASE_FIELD}</b>&nbsp;
        {sugar_help text=$MOD.LBL_DATABASE_FIELD_HELP}
    </td>
    <td style="text-align: left;" scope="row">
        <b>{$MOD.LBL_ROW} 1</b>&nbsp;
        {sugar_help text=$MOD.LBL_ROW_HELP}
    </td>
    {if $HAS_HEADER != 'on'}
    <td style="text-align: left;" scope="row"><b>{$MOD.LBL_ROW} 2</b></td>
    {/if}
    <td style="text-align: left;" scope="row" id="default_column_header">
        <a id="hide_default_link">{sugar_image image="advanced_search.gif" name="advanced_search" height="8" width="8" align="top"}</a>
        <span id="default_column_header_span"><b id="">{$MOD.LBL_DEFAULT_VALUE}</b>&nbsp;
        {sugar_help text=$MOD.LBL_DEFAULT_VALUE_HELP}</span>
    </td>
</tr>
{/if}
<tr>
    {if $HAS_HEADER == 'on'}
    <td id="row_{$smarty.foreach.rows.index}_header">{$item.cell1}</td>
    {/if}
    <td valign="top" align="left" id="row_{$smarty.foreach.rows.index}_col_0">
        <select class='fixedwidth' name="colnum_{$smarty.foreach.rows.index}">
            <option value="-1">{$MOD.LBL_DONT_MAP}</option>
            {$item.field_choices}
        </select>
    </td>
    {if $item.show_remove}
    <td colspan="2">
        <input title="{$MOD.LBL_REMOVE_ROW}" accessKey=""
            id="deleterow_{$smarty.foreach.rows.index}" class="button" type="button"
            value="  {$MOD.LBL_REMOVE_ROW}  ">
    </td>
    {else}
    {if $HAS_HEADER != 'on'}
    <td id="row_{$smarty.foreach.rows.index}_col_1" scope="row">{$item.cell1}</td>
    {/if}
    <td id="row_{$smarty.foreach.rows.index}_col_2" scope="row">{$item.cell2}</td>
    {/if}
    <td id="defaultvaluepicker_{$smarty.foreach.rows.index}" nowrap="nowrap">
        {$item.default_field}
    </td>
</tr>
{/foreach}
<tr>
    <td align="left" colspan="4" style="background: transparent;">
        <input title="{$MOD.LBL_ADD_ROW}" accessKey="" id="addrow" class="button" type="button"
            name="button" value="  {$MOD.LBL_ADD_ROW}  ">
        <input title="{$MOD.LBL_SHOW_ADVANCED_OPTIONS}" accessKey="" id="toggleImportOptions" class="button" type="button"
            name="button" value="  {$MOD.LBL_SHOW_ADVANCED_OPTIONS}  ">
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    </td>
</tr>
<tr style="display: none;" id="importOptions">
    <td valign="middle" colspan="4">
        <table border="0" width="100%">
        <tr>
            <td valign="top" width="50%">
                <div>
                    <h4>{$MOD.LBL_IMPORT_FILE_SETTINGS}&nbsp;{sugar_help text=$MOD.LBL_IMPORT_FILE_SETTINGS_HELP}</h4>
                    <table border=0 class="edit view">


                    <tr>
                        <td scope="row"><slot>{$MOD.LBL_NUMBER_GROUPING_SEP}</slot></td>
                        <td ><slot>
                            <input tabindex='4' name='importlocale_num_grp_sep' id='default_number_grouping_seperator'
                                type='text' maxlength='1' size='1' value='{$NUM_GRP_SEP}'
                                onkeydown='setSigDigits();' onkeyup='setSigDigits();'>
                        </slot></td>
                    </tr>
                    <tr>
                        <td scope="row"><slot>{$MOD.LBL_DECIMAL_SEP}</slot></td>
                        <td ><slot>
                            <input tabindex='4' name='importlocale_dec_sep' id='default_decimal_seperator'
                                type='text' maxlength='1' size='1' value='{$DEC_SEP}'
                                onkeydown='setSigDigits();' onkeyup='setSigDigits();'>
                        </slot></td>
                    </tr>
                    <tr>
                        <td scope="row" valign="top">{$MOD.LBL_LOCALE_DEFAULT_NAME_FORMAT}: </td>
                        <td  valign="top">
                            <input onkeyup="setPreview();" onkeydown="setPreview();" id="default_locale_name_format" type="text" tabindex='4' name="importlocale_default_locale_name_format" value="{$default_locale_name_format}">
                           <br />{$MOD.LBL_LOCALE_NAME_FORMAT_DESC}
                        </td>
                    </tr>
                    <tr>
                        <td scope="row" valign="top"><i>{$MOD.LBL_LOCALE_EXAMPLE_NAME_FORMAT}:</i> </td>
                        <td  valign="top"><input tabindex='4' id="nameTarget" name="no_value" id=":q" value="" style="border: none;" disabled size="50"></td>
                    </tr>
                    </table>
                </div>
            </td>
        </tr>
        </table>
    </td>
</tr>
</table>
{$JAVASCRIPT_CHOOSER}

{if $NOTETEXT != '' || $required_fields != ''}
<p>
<b>{$MOD.LBL_NOTES}</b>
<ul>
<li>{$MOD.LBL_REQUIRED_NOTE}{$required_fields}</li>
{$NOTETEXT}
</ul>
</p>
{/if}

<br />
<table width="100%" cellpadding="2" cellspacing="0" border="0">
<tr>
    <td align="left">
        <input title="{$MOD.LBL_BACK}" accessKey="" id="goback" class="button" type="submit" name="button" value="  {$MOD.LBL_BACK}  ">&nbsp;
        <input title="{$MOD.LBL_NEXT}" accessKey="" id="gonext" class="button" type="submit" name="button" value="  {$MOD.LBL_NEXT}  ">
    </td>
</tr>
</table>

</form>
{$JAVASCRIPT}
{literal}
<script type="text/javascript" language="Javascript">
enableQS(false);
{/literal}{$getNameJs}{literal}
{/literal}{$getNumberJs}{literal}
	setSymbolValue(document.getElementById('currency_select').selectedIndex);
	setSigDigits();

{/literal}{$confirmReassignJs}{literal}
</script>
{/literal}
