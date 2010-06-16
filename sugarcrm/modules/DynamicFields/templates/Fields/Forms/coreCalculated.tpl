{*
//FILE SUGARCRM flav=een ONLY
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

// $Id: enum.tpl 35784 2008-05-20 21:31:40Z dwheeler $

*}

<tr>
	<td class='mbLBL'>{sugar_translate label="LBL_CALCULATED"}:</td>
	<td>
		<input type="checkbox" name="calc_check" value="0" {if !empty($vardef.formula)}checked{/if} 
		onclick="toggleDisplay('{$vardef.name}_formula');toggleDisplay('{$vardef.name}_enforced');"/>
	</td>
</tr>
<tr id='{$vardef.name}_formula' {if empty($vardef.formula)}style='display:none'{/if} >
	<td class='mbLBL'>{sugar_translate label="LBL_FORMULA"}:</td>
	<td>
		<input id="formula" type="text" name="formula" value="{$vardef.formula|escape:'html'}" maxlength=255 />
         <input class="button" type=button name="editFormula" value="{sugar_translate label="LBL_BTN_EDIT_FORMULA"}" 
            onclick="ModuleBuilder.moduleLoadFormula(YAHOO.util.Dom.get('formula').value)"/>
    </td>
</tr>
<tr id='{$vardef.name}_enforced' {if empty($vardef.enforced)}style="display:none"{/if}><td class='mbLBL'>Enforced:</td>
    <td><input type="checkbox" name="enforced" id="enforced" value="1" onclick="ModuleBuilder.toggleEnforced();"{if !empty($vardef.enforced)}CHECKED{/if} {if $hideLevel > 5}disabled{/if}/>
        {if $hideLevel > 5}<input type="hidden" name="enforced" value="{$vardef.enforced}">{/if}
    </td>
</tr>