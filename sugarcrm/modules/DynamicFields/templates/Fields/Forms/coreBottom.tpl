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

// $Id: coreBottom.tpl 56676 2010-05-25 21:33:32Z dwheeler $

*}
{* //BEGIN SUGARCRM flav=een ONLY *}
{*<tr><td class='mbLBL'>Dependent:</td>
    <td><input type="checkbox" name="dependent" id="dependent" value="1" onclick ="ModuleBuilder.toggleDF()"
        {if !empty($vardef.dependency)}CHECKED{/if} {if $hideLevel > 5}disabled{/if}/>
    </td>
</tr>
<tr id='visFormulaRow' {if empty($vardef.dependency)}style="display:none"{/if}><td class='mbLBL'>Visible If:</td> 
    <td><input id="dependency" type="text" name="dependency" value="{$vardef.dependency|escape:'html'}" maxlength="255" />
          <input class="button" type=button name="editFormula" value="{sugar_translate label="LBL_BTN_EDIT_FORMULA"}" 
            onclick="ModuleBuilder.moduleLoadFormula(YAHOO.util.Dom.get('dependency').value, 'dependency')"/> 
    </td>
</tr>
<tr><td class='mbLBL'>Calculated:</td>
    <td><input type="checkbox" name="calculated" id="calculated" value="1" onclick ="ModuleBuilder.toggleCF()"
        {if !empty($vardef.calculated)}CHECKED{/if} {if $hideLevel > 5}disabled{/if}/>
        {if $hideLevel > 5}<input type="hidden" name="calculated" value="{$vardef.calculated}">{/if}
    </td>
</tr>
<tr id='formulaRow' {if empty($vardef.formula)}style="display:none"{/if}><td class='mbLBL'>Formula:</td> 
    <td><input id="formula" type="text" name="formula" value="{$vardef.formula|escape:'html'}" maxlength=255 />
          <input class="button" type=button name="editFormula" value="{sugar_translate label="LBL_BTN_EDIT_FORMULA"}" 
            onclick="ModuleBuilder.moduleLoadFormula(YAHOO.util.Dom.get('formula').value)"/> 
    </td>
</tr>
<tr id='enforcedRow' {if empty($vardef.enforced)}style="display:none"{/if}><td class='mbLBL'>Enforced:</td>
    <td><input type="checkbox" name="enforced" id="enforced" value="1" onclick="ModuleBuilder.toggleEnforced();"{if !empty($vardef.enforced)}CHECKED{/if} {if $hideLevel > 5}disabled{/if}/>
        {if $hideLevel > 5}<input type="hidden" name="enforced" value="{$vardef.enforced}">{/if}
    </td>
</tr>*}
{* //END SUGARCRM flav=een ONLY *}
{if $vardef.type != 'bool'}
<tr ><td class='mbLBL'>{sugar_translate module="DynamicFields" label="COLUMN_TITLE_REQUIRED_OPTION"}:</td><td><input type="checkbox" name="required" value="1" {if !empty($vardef.required)}CHECKED{/if} {if $hideLevel > 5}disabled{/if}/>{if $hideLevel > 5}<input type="hidden" name="required" value="{$vardef.required}">{/if}</td></tr>
{/if}
{* //BEGIN SUGARCRM flav=pro ONLY*}
<tr>
{if !$hideReportable}
<td class='mbLBL'>{sugar_translate module="DynamicFields" label="COLUMN_TITLE_REPORTABLE"}:</td>
<td>
	<input type="checkbox" name="reportableCheckbox" value="1" {if !empty($vardef.reportable)}CHECKED{/if} {if $hideLevel > 5}disabled{/if} 
	onClick="document.getElementById('reportable').value=this.checked"/>
	<input type="hidden" name="reportable" id="reportable" value="{$vardef.reportable}">
</td>
</tr>
{/if}
{* //END SUGARCRM flav=pro ONLY*}
<tr><td class='mbLBL'>{sugar_translate module="DynamicFields" label="COLUMN_TITLE_AUDIT"}:</td><td><input type="checkbox" name="audited" value="1" {if !empty($vardef.audited) }CHECKED{/if} {if $hideLevel > 5}disabled{/if}/>{if $hideLevel > 5}<input type="hidden" name="audited" value="{$vardef.audited}">{/if}</td></tr>
{if !$hideImportable}
<tr><td class='mbLBL'>{sugar_translate module="DynamicFields" label="COLUMN_TITLE_IMPORTABLE"}:</td><td>
    {if $hideLevel < 5}
        {html_options name="importable" id="importable" selected=$vardef.importable options=$importable_options}
        {sugar_help text=$mod_strings.LBL_POPHELP_IMPORTABLE FIXX=260 FIXY=300}
    {else}
        {if isset($vardef.importable)}{$importable_options[$vardef.importable]}
        {else}{$importable_options.true}{/if}
    {/if}
</td></tr>
{/if}
{if !$hideDuplicatable}
<tr><td class='mbLBL'>{sugar_translate module="DynamicFields" label="COLUMN_TITLE_DUPLICATE_MERGE"}:</td><td>
{if $vardef.type != 'multienum'}
	{if $hideLevel < 5}
    	{html_options name="duplicate_merge" id="duplicate_merge" selected=$vardef.duplicate_merge_dom_value options=$duplicate_merge_options}
    	{sugar_help text=$mod_strings.LBL_POPHELP_DUPLICATE_MERGE FIXX=260 FIXY=0}
	{else}
    	{if isset($vardef.duplicate_merge_dom_value)}{$vardef.duplicate_merge_dom_value}
    	{else}{$duplicate_merge_options[0]}{/if}
	{/if}
{else}
	{$duplicate_merge_options[0]}
{/if}
</td></tr>
{/if}
</table>