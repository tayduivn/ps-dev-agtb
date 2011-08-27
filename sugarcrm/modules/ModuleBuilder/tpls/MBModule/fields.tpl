{*
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
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
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
*}
<div id='studiofields'>
<input type='button' name='addfieldbtn' value='{$mod_strings.LBL_BTN_ADDFIELD}' class='button' onclick='ModuleBuilder.moduleLoadField("");'>&nbsp;
{if $editLabelsMb=='1'}
<input type='button' name='addfieldbtn' value='{$mod_strings.LBL_BTN_EDLABELS}' class='button' onclick='ModuleBuilder.moduleLoadLabels("mb");'>
{else}
<input type='button' name='addfieldbtn' value='{$mod_strings.LBL_BTN_EDLABELS}' class='button' onclick='ModuleBuilder.moduleLoadLabels("studio");'>
{/if}

<table width="100%">

    <tr>
    <td>Name</td>
    <td>ID</td>
    <td>Type</td>
    <td nowrap>Custom</td>
    <td nowrap>Required</td>
    <td nowrap>Global Search</td>
    </tr>

{foreach from=$module->mbvardefs->vardefs.fields key='group' item='fields'}
    {counter name='items' assign='items' start=0}
    {foreach from=$fields key='name' item='def'}
            <tr>
            <td><a class='mbLBLL' href='javascript:void(0)' onclick='ModuleBuilder.moduleLoadField("{$name}")'>{$def.label}</a></td>
            <td>{$name}</td>
            <td>{$def.type}</td>
            <td><input type="checkbox" DISABLED {if $group == 'CUSTOM'}CHECKED{/if}></td>
            <td><input type="checkbox" DISABLED {if $def.required}CHECKED{/if}></td>
            <td><input type="checkbox" DISABLED {if $def.unified_search}CHECKED{/if}></td>
            {counter name='items'}
            </tr>
    {/foreach}
{/foreach}
</table>


<script>
ModuleBuilder.module = '{$module->name}';
ModuleBuilder.MBpackage = '{$package->name}';
ModuleBuilder.helpRegisterByID('studiofields', 'input');
{if $package->name != 'studio'}
ModuleBuilder.helpSetup('fieldsEditor','mbDefault');
{else}
ModuleBuilder.helpSetup('fieldsEditor','default');
{/if}
</script>