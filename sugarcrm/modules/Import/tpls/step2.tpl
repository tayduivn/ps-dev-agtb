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

// $Id: step2.tpl 25541 2007-01-11 21:57:54Z jmertic $

*}
{overlib_includes}
{$MODULE_TITLE}
<form enctype="multipart/form-data" name="importstep2" method="POST" action="index.php" id="importstep2">
<input type="hidden" name="module" value="Import">
<input type="hidden" name="custom_delimiter" value="{$CUSTOM_DELIMITER}">
<input type="hidden" name="custom_enclosure" value="{$CUSTOM_ENCLOSURE}">
<input type="hidden" name="source" value="{$SOURCE}">
<input type="hidden" name="source_id" value="{$SOURCE_ID}">
<input type="hidden" name="action" value="Step3">
<input type="hidden" name="current_step" value="{$CURRENT_STEP}">
<input type="hidden" name="import_module" value="{$IMPORT_MODULE}">
{foreach from=$instructions key=key item=item name=instructions}
{if $smarty.foreach.instructions.first}
<table width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
	<td align="left"><p>{$INSTRUCTIONS_TITLE}</p></td>
</tr>
<tr>
	<td>
	<table width="50%">
{/if}
	<tr>
		<td valign="top"><b>{$item.STEP_NUM}</b></td>
		<td>{$item.INSTRUCTION_STEP}</td>
	</tr>
{if $smarty.foreach.instructions.last}
    </table>
	</td>
</tr>
</table>
{/if}
{/foreach}

<br>

<table width="100%" border="0" cellspacing="0" cellpadding="0" class="edit view">
<tr>
<td>
	<table border="0" cellspacing="0" cellpadding="0" width="100%">
        <tr>
            <td align="left" scope="row" colspan="4">{$MOD.LBL_SELECT_FILE}</td>
        </tr>
        <tr>
            <td scope="row"><input type="hidden" /><input size="60" name="userfile" type="file"/></td>
        </tr>
        <tr>
            <td scope="row" colspan="3">
                <h3>{$MOD.LBL_IMPORT_TYPE}&nbsp;<span class="required">*</span></h3></td>
          </tr>
          <tr>
            <td scope="row" colspan="3">
                <input class="radio" type="radio" name="type" value="import" checked="checked" />
                &nbsp;{$MOD.LBL_IMPORT_BUTTON}
            </td>
          </tr>
          <tr>
            <td scope="row" colspan="3">
                <input class="radio" type="radio" name="type" value="update" />
                &nbsp;{$MOD.LBL_UPDATE_BUTTON}
            </td>
          </tr>
	</table>
    <table border="0" cellspacing="0" cellpadding="0" width="100%">
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
          <tr id="custom_import_{$smarty.foreach.saved.index}">
            <td scope="row" colspan="2">
                <input class="radio" type="radio" name="source" value="custom:{$item.IMPORT_ID}"/>
                &nbsp;{$item.IMPORT_NAME}
            </td>
            <td scope="row">
                {if $is_admin}
                <input type="button" name="publish" value="{$MOD.LBL_PUBLISH}" class="button" publish="yes"
                    onclick="publishMapping(this, 'yes','{$item.IMPORT_ID}');">
                {/if}
                <input type="button" name="delete" value="{$MOD.LBL_DELETE}" class="button"
					onclick="if(confirm('{$MOD.LBL_DELETE_MAP_CONFIRMATION}')){literal}{{/literal} deleteMapping('custom_import_{$smarty.foreach.saved.index}', '{$item.IMPORT_ID}' );{literal}}{/literal}">
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
          <tr id="published_import_{$smarty.foreach.published.index}">
            <td scope="row" colspan="2">
                <input class="radio" type="radio" name="source" value="custom:{$item.IMPORT_ID}"/>
                &nbsp;{$item.IMPORT_NAME}
            </td>
            <td scope="row">
                {if $is_admin}
                <input type="button" name="publish" value="{$MOD.LBL_UNPUBLISH}" class="button" publish="no"
                    onclick="publishMapping(this, 'no','{$item.IMPORT_ID}');">
                <input type="button" name="delete" value="{$MOD.LBL_DELETE}" class="button"
                    onclick="if(confirm('{$MOD.LBL_DELETE_MAP_CONFIRMATION}')){literal}{{/literal}deleteMapping('published_import_{$smarty.foreach.published.index}','{$item.IMPORT_ID}' );{literal}}{/literal}">
                {/if}
            </td>
          </tr>
          {/foreach}
    </table>
</td>
</tr>
</table>

<br>
<table width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
	<td align="left">
        {if $displayBackBttn}
            <input title="{$MOD.LBL_BACK}" accessKey="" class="button" type="submit" name="button" value="  {$MOD.LBL_BACK}  " id="goback">&nbsp;
        {/if}
	    <input title="{$MOD.LBL_NEXT}" accessKey="" class="button" type="submit" name="button" value="  {$MOD.LBL_NEXT}  " id="gonext">
    </td>
</tr>
</table>

</form>
{$JAVASCRIPT}
