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

.impSample {
    word-wrap: break-word;
}
-->
</style>
{/literal}
<script type="text/javascript" src="{sugar_getjspath file='include/javascript/sugar_grp_yui_widgets.js'}"></script>
{overlib_includes}
{$MODULE_TITLE}
<form enctype="multipart/form-data" real_id="importconfirm" id="importconfirm" name="importconfirm" method="POST" action="index.php">
<input type="hidden" name="module" value="Import">
<input type="hidden" name="custom_delimiter" value="{$CUSTOM_DELIMITER}">
<input type="hidden" name="custom_enclosure" value="{$CUSTOM_ENCLOSURE}">
<input type="hidden" name="type" value="{$TYPE}">
<input type="hidden" name="source" value="{$SOURCE}">
<input type="hidden" name="source_id" value="{$SOURCE_ID}">
<input type="hidden" name="action" value="Step3">
<input type="hidden" name="import_module" value="{$IMPORT_MODULE}">
<input type="hidden" name="import_type" value="{$TYPE}">
<input type="hidden" name="file_name" value="{$FILE_NAME}">
<input type="hidden" name="current_step" value="{$CURRENT_STEP}">
    
<div id="confirm_table">
    {include file='modules/Import/tpls/confirm_table.tpl'}
</div>

<div>
    <h4>{$MOD.LBL_IMPORT_FILE_SETTINGS}&nbsp;{sugar_help text=$MOD.LBL_IMPORT_FILE_SETTINGS_HELP}</h4>
    <table border=0 class="edit view">
        <tr>
            <td scope="row">
                <slot>{$MOD.LBL_CHARSET}</slot>
            </td>
            <td>
                <slot><select tabindex='4' name='importlocale_charset'>{$CHARSETOPTIONS}</select></slot>
            </td>
        </tr>
        <tr>
            <td scope="row">
                <slot>{$MOD.LBL_CUSTOM_DELIMITER}</slot>
            </td>
            <td>
                <slot><input type="text" id="custom_delimiter" name="custom_delimiter" value="{$CUSTOM_DELIMITER}" style="width: 5em;" maxlength="1" /></slot>
            </td>
        </tr>
        <tr>
            <td scope="row">
                <slot>{$MOD.LBL_CUSTOM_ENCLOSURE}</slot>
            </td>
            <td>
                <slot>
                    <select name="custom_enclosure" id="custom_enclosure">
                        <option value="&quot;" selected="selected">{$MOD.LBL_OPTION_ENCLOSURE_DOUBLEQUOTE}</option>
                        <option value="'">{$MOD.LBL_OPTION_ENCLOSURE_QUOTE}</option>
                        <option value="">{$MOD.LBL_OPTION_ENCLOSURE_NONE}</option>
                        <option value="other">{$MOD.LBL_OPTION_ENCLOSURE_OTHER}</option>
                    </select>
                    <input type="text" name="custom_enclosure_other" id="custom_enclosure_other" style="display: none; width: 5em;" maxlength="1" />
                    {sugar_help text=$MOD.LBL_ENCLOSURE_HELP}
                </slot>
            </td>
        </tr>

        <tr>
            <td scope="row">
                {$MOD.LBL_HAS_HEADER}
            </td>
            <td>
                <input class="checkBox" value='on' type="checkbox" name="has_header" id="has_header" {$HAS_HEADER_CHECKED}>
            </td>
	    </tr>



        <tr>
            <td scope="row"><slot>{$MOD.LBL_DATE_FORMAT}</slot></td>
            <td ><slot><select tabindex='4' name='importlocale_dateformat'>{$DATEOPTIONS}</select></slot></td>
        </tr>
        <tr>
            <td scope="row"><slot>{$MOD.LBL_TIME_FORMAT}</slot></td>
            <td ><slot><select tabindex='4' name='importlocale_timeformat'>{$TIMEOPTIONS}</select></slot></td>
        </tr>
        <tr>
            <td scope="row"><slot>{$MOD.LBL_TIMEZONE}</slot></td>
            <td ><slot><select tabindex='4' name='importlocale_timezone'>{html_options options=$TIMEZONEOPTIONS selected=$TIMEZONE_CURRENT}</select></slot></td>
        </tr>

        <tr>
            <td scope="row"><slot>{$MOD.LBL_CURRENCY}</slot></td>
            <td ><slot>
                <select tabindex='4' id='currency_select' name='importlocale_currency' onchange='setSymbolValue(this.selectedIndex);setSigDigits();'>{$CURRENCY}</select>
                <input type="hidden" id="symbol" value="">
            </slot></td>
        </tr>

        
        <tr>
            <td scope="row" colspan="3">
                <h5>{$MOD.LBL_THIRD_PARTY_CSV_SOURCES}&nbsp;{sugar_help text=$MOD.LBL_THIRD_PARTY_CSV_SOURCES_HELP}</h5></td>
          </tr>
        <tr>
            <td colspan="2" scope="row"><input class="radio" type="radio" name="source" value="salesforce" id='sf_map' />
            &nbsp;{$MOD.LBL_SALESFORCE}</td>
        </tr>
        <tr>
            <td colspan="2" scope="row"><input class="radio" type="radio" name="source" value="outlook" id='outlook_map'/>
            &nbsp;{$MOD.LBL_MICROSOFT_OUTLOOK}</td>
        </tr>
        
    </table>
</div>


    <table width="100%" cellpadding="2" cellspacing="0" border="0">
<tr>
    <td align="left">
        <input title="{$MOD.LBL_BACK}" accessKey="" id="goback" class="button" type="submit" name="button" value="  {$MOD.LBL_BACK}  ">&nbsp;
        <input title="{$MOD.LBL_NEXT}" accessKey="" class="button" type="submit" name="button" value="  {$MOD.LBL_NEXT}  " id="gonext">
    </td>
</tr>
</table>

</form>

{$JAVASCRIPT}

{literal}
<script type="text/javascript" language="Javascript">
{/literal}{$currencySymbolJs}{literal}
</script>
{/literal}