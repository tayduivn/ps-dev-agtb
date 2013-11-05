{*
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
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/*********************************************************************************
 * $Id: EditView.html 53409 2010-01-04 03:31:15Z roger $
 ********************************************************************************/
*}

<script type="text/javascript">
    js_iso4217 = {$JS_ISO4217};
    function confirm_delete(form) {$smarty.ldelim}
        if (confirm('{$MOD.NTC_DELETE_CONFIRMATION}')) {$smarty.ldelim}
            form.edit.value='false';
            form.action.value='Delete';
            return true;
        {$smarty.rdelim}
        return false;
    {$smarty.rdelim}
</script>
<script type="text/javascript" src="{sugar_getjspath file='modules/Currencies/EditView.js'}"></script>
<table width="100%" cellspacing="0" cellpadding="0" border="0" class="edit view">
<tr>
    <td>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
<td width="15%" scope="row" nowrap><slot>{$MOD.LBL_LIST_NAME}: <span class="required">{$APP.LBL_REQUIRED_SYMBOL}</span></slot></td>
<td width="35%"><slot><input name='name' tabindex='1' size='30' maxlength='50' type="text" value="{$NAME}"></slot></td>
<td width="15%" scope="row" nowrap><slot>{$MOD.LBL_LIST_ISO4217}:&nbsp;{sugar_help text=$MOD.LBL_LIST_ISO4217_HELP}</slot></td>
<td width="35%"><slot><input name='iso4217' tabindex='1' size='3'
  maxlength='3' type="text" value="{$ISO4217}" onKeyUp='isoUpdate(this);'></slot></td>
</tr>
<tr>

</tr>
<tr>
<td width="15%" scope="row" nowrap><slot> {$MOD.LBL_LIST_RATE}: <span class="required">{$APP.LBL_REQUIRED_SYMBOL}</span></slot></td>
<td width="35%"><slot><input name='conversion_rate' tabindex='1' size='30' maxlength='50' type="text" value="{$CONVERSION_RATE}">
{sugar_help text=$MOD.LBL_LIST_RATE_HELP }
</slot></td>
<td width="15%" scope="row" nowrap><slot>{$MOD.LBL_LIST_SYMBOL}: <span class="required">{$APP.LBL_REQUIRED_SYMBOL}</span></slot></td>
<td width="35%"><slot><input name='symbol' tabindex='1' size='3' maxlength='50' type="text" value="{$SYMBOL}"></slot></td>

</tr>
<tr>

</tr>
<tr>
<td scope="row"><slot>{$MOD.LBL_LIST_STATUS}:</slot></td>
<td><slot><select name='status' tabindex='1'>{$STATUS_OPTIONS}</select> <em>{$MOD.NTC_STATUS}</em></slot></td>
</tr></table>
</td>
</tr>
</table>
<table>
    <tr>
        <td>
            <input type='hidden' name='record' value='{$ID}'>
            <input title="{$APP.LBL_SAVE_BUTTON_TITLE}" accessKey="{$APP.LBL_SAVE_BUTTON_KEY}" class="button" onclick="this.form.edit.value='true';this.form.action.value='index';return check_form('EditView');" type="submit" name="button" value="{$APP.LBL_SAVE_BUTTON_LABEL}">
            {if $ID ne ""}<input title="{$APP.LBL_DELETE_BUTTON_TITLE}" accessKey="{$APP.LBL_DELETE_BUTTON_KEY}" class="button" onclick="return confirm_delete(this.form);" type="submit" name="button" value="{$APP.LBL_DELETE_BUTTON_LABEL}">{/if}
            <input title="{$APP.LBL_CANCEL_BUTTON_TITLE}" accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}" class="button" onclick="this.form.edit.value='false';this.form.action.value='index';" type="submit" name="button" value="{$APP.LBL_CANCEL_BUTTON_LABEL}">
        </td>
    </tr>
</table>
</form>
{$JAVASCRIPT}
{if $REFRESHMETADATA}
<script type="text/javascript">
    // ping sidecar to force a fresh metadata hit if there was a change that requires it
    var app = parent.SUGAR.App;
    app.api.call('read', app.api.buildURL('ping'));
</script>
{/if}
