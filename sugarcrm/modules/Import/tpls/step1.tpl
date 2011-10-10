{*

/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/en/msa/master_subscription_agreement_11_April_2011.pdf
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
 * by SugarCRM are Copyright (C) 2006 SugarCRM, Inc.; All Rights Reserved.
 */
*}

{literal}
<style>
#smtpButtonGroup .yui-button {
    padding-top: 10px;
}
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
<<<<<<< HEAD
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
                                    <td align="left" scope="row" colspan="3"><div class="hr">&nbsp;</div></td>
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
                            <tr id="ext_source_help">
                                <td align="left" scope="row" colspan="3"><h3>{$MOD.LBL_WHAT_IS}&nbsp;</h3></td>
                            </tr>
                            <tr id="ext_source_csv">
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
=======
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="edit view">
<tr>
    <td>
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td valign="top" width='50%' scope="row"><table border="0" cellpadding="0" cellspacing="0">
          <tr>
            <td align="left" scope="row" colspan="3"><h3>{$MOD.LBL_WHAT_IS}&nbsp;<span class="required">*</span></h3></td>
          </tr>
          <tr>
            <td colspan="3" scope="row"><input class="radio" type="radio" name="source" id="source_csv" value="csv" checked="checked" {if $selectedData->source == 'csv'}checked="checked"{/if} />
              &nbsp;{$MOD.LBL_CSV}&nbsp;{sugar_help text=$MOD.LBL_DELIMITER_COMMA_HELP}</td>
          </tr>
          <tr id="customEnclosure">
            <td scope="row">&nbsp;&nbsp;{$MOD.LBL_CUSTOM_ENCLOSURE}</td>
            <td colspan="2" scope="row">
                <select name="custom_enclosure" id="custom_enclosure">
                    <option value="&quot;" {if $selectedData->custom_enclosure == '&quot;' or !$selectedData->custom_enclosure}selected="selected"{/if}>{$MOD.LBL_OPTION_ENCLOSURE_DOUBLEQUOTE}</option>
                    <option value="'" {if $selectedData->custom_enclosure == "'" or $selectedData->custom_enclosure == '&#039;'}selected="selected"{/if}>{$MOD.LBL_OPTION_ENCLOSURE_QUOTE}</option>
                    <option value="" {if isset($selectedData->custom_enclosure) and $selectedData->custom_enclosure == ""}selected="selected"{/if}>{$MOD.LBL_OPTION_ENCLOSURE_NONE}</option>
                    <option value="other" {if $selectedData->custom_other}selected="selected"{/if}>{$MOD.LBL_OPTION_ENCLOSURE_OTHER}</option>
                </select>
                <input type="text" name="custom_enclosure_other" style="{if !$selectedData->custom_other}display: none;{/if} width: 5em;" maxlength="1" value="{$selectedData->custom_enclosure}" />
                {sugar_help text=$MOD.LBL_ENCLOSURE_HELP}
            </td>
          </tr>
          <tr>
            <td colspan="3" scope="row"><input class="radio" type="radio" name="source" id="source_tab" value="tab" {if $selectedData->source == 'tab'}checked="checked"{/if} />
              &nbsp;{$MOD.LBL_TAB}&nbsp;{sugar_help text=$MOD.LBL_DELIMITER_TAB_HELP}</td>
          </tr>
          <tr>
            <td colspan="3" scope="row"><input class="radio" type="radio" name="source" id="source_other" value="other" {if $selectedData->source == 'other'}checked="checked"{/if}/>
              &nbsp;{$MOD.LBL_CUSTOM_DELIMITED}&nbsp;{sugar_help text=$MOD.LBL_DELIMITER_CUSTOM_HELP}</td>
          </tr>
          <tr id="customDelimiter" style='display:none'>
            <td scope="row">&nbsp;&nbsp;{$MOD.LBL_CUSTOM_DELIMITER}&nbsp;<span class="required">*</span></td>
            <td colspan="2" scope="row">
                <input type="text" name="custom_delimiter" value="{$selectedData->custom_delimiter}" style="width: 5em;" maxlength="1" />
            </td>
          </tr>
{* //BEGIN SUGARCRM flav!=sales ONLY *}
          {if $show_salesforce}
          <tr>
            <td colspan="3" scope="row"><input class="radio" type="radio" name="source" id="source_salesforce" value="salesforce" {if $selectedData->source == 'salesforce'}checked="checked"{/if}/>
            &nbsp;{$MOD.LBL_SALESFORCE}</td>
            </tr>
          {/if}
          {if $show_outlook}
          <tr>
            <td colspan="3" scope="row"><input class="radio" type="radio" name="source" id="source_outlook" value="outlook" {if $selectedData->source == 'outlook'}checked="checked"{/if}/>
              &nbsp;{$MOD.LBL_MICROSOFT_OUTLOOK}</td>
            </tr>
          {/if}
          {if $show_act}
          <tr>
            <td colspan="3" scope="row"><input class="radio" type="radio" name="source" id="source_act" value="act" {if $selectedData->source == 'act'}checked="checked"{/if}/>
              &nbsp;{$MOD.LBL_ACT}</td>
          </tr>
          {/if}
{* //END SUGARCRM flav!=sales ONLY *}
          {foreach from=$custom_mappings item=item name=custommappings}
          {capture assign=mapping_label}{$MOD.LBL_CUSTOM_MAPPING_}{$item|upper}{/capture}
          <tr>
            <td colspan="3" scope="row"><input class="radio" type="radio" name="source" value="{$item}" />
              &nbsp;{$mapping_label}</td>
          </tr>
          {/foreach}
          {foreach from=$custom_imports key=key item=item name=saved}
          {if $smarty.foreach.saved.first}
          <tr>
            <td scope="row" colspan="3">
                <h5>{$MOD.LBL_MY_SAVED}&nbsp;{sugar_help text=$MOD.LBL_MY_SAVED_HELP}</h5></td>
          </tr>
          {/if}
          <tr>
            <td scope="row" colspan="2">
                <input class="radio" type="radio" name="source" value="custom:{$item.IMPORT_ID}"/>
                &nbsp;{$item.IMPORT_NAME}
            </td>
            <td scope="row">
                {if $is_admin}
                <input type="button" name="publish" value="{$MOD.LBL_PUBLISH}" class="button" 
                    onclick="document.location.href = 'index.php?publish=yes&amp;import_module={$IMPORT_MODULE}&amp;module=Import&amp;action=step1&amp;import_map_id={$item.IMPORT_ID}'">
                {/if}
                <input type="button" name="delete" value="{$MOD.LBL_DELETE}" class="button" 
					onclick="if(confirm('{$MOD.LBL_DELETE_MAP_CONFIRMATION}')){literal}{{/literal}document.location.href = 'index.php?import_module={$IMPORT_MODULE}&amp;module=Import&amp;action=step1&amp;delete_map_id={$item.IMPORT_ID}'{literal}}{/literal}">
            </td>
          </tr>
          {/foreach}
          {foreach from=$published_imports key=key item=item name=published}
          {if $smarty.foreach.published.first}
          <tr>
            <td scope="row" colspan="3">
                <h5>{$MOD.LBL_PUBLISHED_SOURCES}&nbsp;{sugar_help text=$MOD.LBL_MY_PUBLISHED_HELP}</h5></td>
          </tr>
          {/if}
          <tr>
            <td scope="row" colspan="2">
                <input class="radio" type="radio" name="source" value="custom:{$item.IMPORT_ID}"/>
                &nbsp;{$item.IMPORT_NAME}
            </td>
            <td scope="row">
                {if $is_admin}
                <input type="button" name="publish" value="{$MOD.LBL_UNPUBLISH}" class="button" 
                    onclick="document.location.href = 'index.php?publish=no&amp;import_module={$IMPORT_MODULE}&amp;module=Import&amp;action=step1&amp;import_map_id={$item.IMPORT_ID}'">
                <input type="button" name="delete" value="{$MOD.LBL_DELETE}" class="button" 
                    onclick="if(confirm('{$MOD.LBL_DELETE_MAP_CONFIRMATION}')){literal}{{/literal}document.location.href = 'index.php?import_module={$IMPORT_MODULE}&amp;module=Import&amp;action=step1&amp;delete_map_id={$item.IMPORT_ID}'{literal}}{/literal}">
                {/if}
            </td>
          </tr>
          {/foreach}
          <tr>
            <td scope="row" colspan="3">
                <h3>{$MOD.LBL_IMPORT_TYPE}&nbsp;<span class="required">*</span></h3></td>
          </tr>
          <tr>
            <td scope="row" colspan="3">
                <input class="radio" id="action_create" type="radio" name="type" value="import" {if $selectedData->type == 'import' or !$selectedData->type}checked="checked"{/if} />
                &nbsp;{$MOD.LBL_IMPORT_BUTTON}
            </td>
          </tr>
          <tr>
            <td scope="row" colspan="3">
                <input class="radio" id="action_create_and_update" type="radio" name="type" value="update" {if $selectedData->type == 'update'}checked="checked"{/if} />
                &nbsp;{$MOD.LBL_UPDATE_BUTTON}
            </td>
        </tr>
    </table>
</p>
<br>
<table width="100%" cellpadding="0" cellspacing="0" border="0">
    <tr>
      <td align="left"><input title="{$MOD.LBL_NEXT}" accessKey="" class="button" type="submit" name="button" value="  {$MOD.LBL_NEXT}  "  id="gonext"></td>
    </tr>
</table>    
</form>
