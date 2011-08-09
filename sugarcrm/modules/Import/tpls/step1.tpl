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

{literal}
<style>
#smtpButtonGroup .yui-radio-button-checked button, .yui-checkbox-button-checked button {
    background-color: #CCCCCC;
    color: #FFFFFF;
    text-shadow: none;
}
{/literal}
</style>
{if $ERROR != ''}
<span class="error">{$ERROR}</span>
{/if}
{$INSTRUCTION}

<form enctype="multipart/form-data" name="importstep1" method="post" action="index.php" id="importstep1">
<input type="hidden" name="module" value="Import">
<input type="hidden" name="action" value="Step2">
<input type="hidden" name="current_step" value="1">
<input type="hidden" name="return_action" value="Step1">
<input type="hidden" name="external_source" value="">
<input type="hidden" name="from_admin_wizard" value="{$FROM_ADMIN}">
<input type="hidden" name="import_module" value="{$IMPORT_MODULE}">
<p>
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
            <td>
                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td valign="top" width='100%' scope="row">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                            {if $showModuleSelection}
                                <tr>
                                    <td align="left" scope="row" colspan="3"><h3>{$MOD.LBL_STEP_MODULE}&nbsp;</h3></td>
                                </tr>
                                <tr>
                                    <td><select tabindex='4' name='admin_import_module' id='admin_import_module'>{$IMPORTABLE_MODULES_OPTIONS}</select></td>
                                </tr>
                                <tr>
                                    <td align="left" scope="row" colspan="3"><div class="hr"></div></td>
                                </tr>
                            {/if}
                            {* //BEGIN SUGARCRM flav=com ONLY *}
                            <tr>
                                <td>
                                    <input type="hidden" name="source" value="csv" />
                                </td>
                            </tr>
                            {* //END SUGARCRM flav=com ONLY *}
                            {* //BEGIN SUGARCRM flav=pro ONLY *}
                            <tr>
                                <td align="left" scope="row" colspan="3"><h3>{$MOD.LBL_WHAT_IS}&nbsp;</h3></td>
                            </tr>
                            <tr>
                                <td colspan="3" scope="row">
                                    <span><input class="radio" type="radio" name="source" value="csv" checked="checked" id="csv_source" />
                                  &nbsp;{$MOD.LBL_CSV}&nbsp;</span>{sugar_help text=$MOD.LBL_DELIMITER_COMMA_HELP}
                                </td>
                            </tr>
                            <tr id="ext_source_tr">
                                <td colspan="3" scope="row"><span><input class="radio" type="radio" name="source" value="external" id="ext_source" />
                  &nbsp;{$MOD.LBL_EXTERNAL_SOURCE}&nbsp;</span>{sugar_help text=$MOD.LBL_EXTERNAL_SOURCE_HELP}
                                </td>
                            </tr>
                            <tr scope="row" id="external_sources_tr" style="display:none;" >
                                <td colspan="2" width="35%" style="padding-top: 10px;">
                                    <div id="smtpButtonGroup" class="yui-buttongroup">
                                    {foreach from=$EXTERNAL_SOURCES key=k item=v}
                                        <span id="{$k}" class="yui-button yui-radio-button{if $selectExternalSource == $k} yui-button-checked{/if}">
                                            <span class="first-child">
                                                <button type="button" name="external_source_button" value="{$k}">
                                                    &nbsp;&nbsp;&nbsp;&nbsp;{$v}&nbsp;&nbsp;&nbsp;&nbsp;
                                                </button>
                                            </span>
                                        </span>
                                    {/foreach}
                                    </div>
                                </td>
                                <td  style="padding-top: 10px;">
                                    <input id="ext_source_sign_in_bttn" type="button" value="{$MOD.LBL_EXT_SOURCE_SIGN_IN}" style="display:none;vertical-align:top; !important">
                                </td>
                            </tr>
                            {* //END SUGARCRM flav=pro ONLY *}
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</p>
</form>