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
{foreach from=$module->mbvardefs->vardefs.fields key='group' item='fields'}
<hr>
<h2>{$title[$group]}</h2>
    <table width='100%'>
    <colgroup span='3' width='33%'>
    <tr>
    {counter name='items' assign='items' start=0}
    {foreach from=$fields key='name' item='def'}
            {if $items % 3 == 0 && $items != 0}
                        </tr><tr>
            {/if}
            <td>
            <a class='mbLBLL' href='javascript:void(0)' onclick='ModuleBuilder.moduleLoadField("{$name}")'>{$name}</a>
            </td>
            {counter name='items'}
    {/foreach}
    {if $items == 0}
        <td class='mbLBLL'>{$mod_strings.LBL_NONE}</td>
    {elseif $items % 3 == 1}
        <td>&nbsp;</td><td>&nbsp;</td>
    {elseif $items % 3 == 2}
        <td>&nbsp;</td>
    {/if}
    </tr>
    </table>
{/foreach}
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