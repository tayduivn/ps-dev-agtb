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

// $Id: coreBottom.tpl 57813 2010-08-19 17:34:44Z kjing $

*}

{* //BEGIN SUGARCRM flav=pro ONLY *}
{if $hideLevel < 5 && $show_fts}
<tr>
    <td class='mbLBL'>{sugar_translate module="DynamicFields" label="COLUMN_TITLE_FTS"}:</td>
    <td>
        {if empty($vardef.full_text_search) || empty($vardef.full_text_search.boost)}
            {html_options name="full_text_search[boost]" id="full_text_search" selected="0" options=$fts_options}
        {else}
            {html_options name="full_text_search[boost]" id="full_text_search" selected=$vardef.full_text_search.boost options=$fts_options}
        {/if}
        {sugar_getimage alt=$mod_strings.LBL_HELP name="helpInline" ext=".gif" other_attributes='id="ftsTipIcon" '}
              <script>
                  if (!ModuleBuilder.ftsToolTip)
                       ModuleBuilder.ftsToolTip = new YAHOO.widget.Tooltip("ftsTipPopup", {ldelim}
                          context:"ftsTipIcon", text:"{$mod_strings.LBL_POPHELP_SEARCHABLE}"
                       {rdelim});
                  else
                      ModuleBuilder.ftsToolTip.cfg.setProperty("context", "ftsTipIcon");
              </script>
    </td>
</tr>
{/if}

{include file='modules/DynamicFields/templates/Fields/Forms/coreDependent.tpl'}
{* //END SUGARCRM flav=pro ONLY *}

{if $vardef.type != 'bool'}
<tr ><td class='mbLBL'>{sugar_translate module="DynamicFields" label="COLUMN_TITLE_REQUIRED_OPTION"}:</td><td><input type="checkbox" name="required" value="1" {if !empty($vardef.required)}CHECKED{/if} {if $hideLevel > 5}disabled{/if}/>{if $hideLevel > 5}<input type="hidden" name="required" value="{$vardef.required}">{/if}</td></tr>
{/if}
{* //BEGIN SUGARCRM flav=pro ONLY*}
<tr>
{if !$hideReportable}
<td class='mbLBL'>{sugar_translate module="DynamicFields" label="COLUMN_TITLE_REPORTABLE"}:</td>
<td>
	<input type="checkbox" name="reportableCheckbox" value="1" {if !empty($vardef.reportable)}CHECKED{/if} {if $hideLevel > 5}disabled{/if} 
	onClick="if(this.checked) document.getElementById('reportable').value=1; else document.getElementById('reportable').value=0;"/>
	<input type="hidden" name="reportable" id="reportable" value="{if !empty($vardef.reportable)}{$vardef.reportable}{else}0{/if}">
</td>
</tr>
{/if}
{* //END SUGARCRM flav=pro ONLY*}
<tr><td class='mbLBL'>{sugar_translate module="DynamicFields" label="COLUMN_TITLE_AUDIT"}:</td><td><input type="checkbox" name="audited" value="1" {if !empty($vardef.audited) }CHECKED{/if} {if $hideLevel > 5}disabled{/if}/>{if $hideLevel > 5}<input type="hidden" name="audited" value="{$vardef.audited}">{/if}</td></tr>

{* //BEGIN SUGARCRM flav=int ONLY *}
{*
{if $globalSearchEnabled == true}
<tr>
    <td class='mbLBL'>{sugar_translate module="DynamicFields" label="COLUMN_TITLE_GLOBAL_SEARCH"}:</td>
    <td><input type="checkbox" name="unified_search" value="1" {if !empty($vardef.unified_search) }CHECKED{/if} {if $hideLevel > 5}disabled{/if}/>
    {if $hideLevel > 5}<input type="hidden" name="unified_search" value="{$vardef.unified_search}">{/if}
    <img id="globalSearchTipIcon" src="{sugar_getimagepath file="helpInline.png"}" />
<script>
	if (!ModuleBuilder.globalSearchToolTip)
		ModuleBuilder.globalSearchToolTip = new YAHOO.widget.Tooltip("globalSearchTipPopup", {ldelim}
		context:"globalSearchTipIcon", text:"{$mod_strings.LBL_POPHELP_GLOBAL_SEARCH}"
		{rdelim});
	else
		ModuleBuilder.globalSearchToolTip.cfg.setProperty("context", "globalSearchTipIcon");
</script>
</td>
</tr>
{/if}
*}
{* //END SUGARCRM flav=int ONLY *}

{if !$hideImportable}
<tr><td class='mbLBL'>{sugar_translate module="DynamicFields" label="COLUMN_TITLE_IMPORTABLE"}:</td><td>
    {if $hideLevel < 5}
        {html_options name="importable" id="importable" selected=$vardef.importable options=$importable_options}
		{sugar_getimage alt=$mod_strings.LBL_HELP name="helpInline" ext=".png" other_attributes='id="importTipIcon" '}
        <script>
            if (!ModuleBuilder.importToolTip)
                 ModuleBuilder.importToolTip = new YAHOO.widget.Tooltip("importTipPopup", {ldelim}
                    context:"importTipIcon", text:"{$mod_strings.LBL_POPHELP_IMPORTABLE}"
                 {rdelim});
            else
                ModuleBuilder.importToolTip.cfg.setProperty("context", "importTipIcon");
        </script>
    {else}
        {if isset($vardef.importable)}{$importable_options[$vardef.importable]}
        {else}{$importable_options.true}{/if}
    {/if}
</td></tr>
{/if}
{if !$hideDuplicatable}
<tr><td class='mbLBL'>{sugar_translate module="DynamicFields" label="COLUMN_TITLE_DUPLICATE_MERGE"}:</td><td>
{if $hideLevel < 5}
    {html_options name="duplicate_merge" id="duplicate_merge" selected=$vardef.duplicate_merge_dom_value options=$duplicate_merge_options}
    {sugar_getimage alt=$mod_strings.LBL_HELP name="helpInline" ext=".png" other_attributes='id="duplicateTipIcon" '}
    <script>
        if (!ModuleBuilder.duplicateToolTip)
             ModuleBuilder.duplicateToolTip = new YAHOO.widget.Tooltip("duplicateTipPopup", {ldelim}
                context:"duplicateTipIcon", text:"{$mod_strings.LBL_POPHELP_DUPLICATE_MERGE}"
             {rdelim});
        else
            ModuleBuilder.duplicateToolTip.cfg.setProperty("context", "duplicateTipIcon");
    </script>
{else}
    {if isset($vardef.duplicate_merge_dom_value)}{$vardef.duplicate_merge_dom_value}
    {else}{$duplicate_merge_options[0]}{/if}
{/if}
</td></tr>
{/if}
</table>
