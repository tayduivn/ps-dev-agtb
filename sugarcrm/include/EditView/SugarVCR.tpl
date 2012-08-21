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
<table border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td nowrap class="paginationWrapper">
            {if !empty($list_link)}
            <button type="button" id="save_and_continue" class="button" title="{$app_strings.LBL_SAVE_AND_CONTINUE}" onClick="this.form.action.value='Save';if(check_form('EditView')){ldelim}sendAndRedirect('EditView', '{$app_strings.LBL_SAVING} {$module}...', '{$list_link}');{rdelim}">
                {$app_strings.LBL_SAVE_AND_CONTINUE}
            </button>
            &nbsp;&nbsp;&nbsp;&nbsp;
            {/if}
            <span class="pagination">
                {if !empty($previous_link)}
                <button type="button" class="button" title="{$app_strings.LNK_LIST_PREVIOUS}" onClick="document.location.href='{$previous_link}';">
                    {sugar_getimage name="previous" attr="border=\"0\" align=\"absmiddle\"" ext=".gif" alt=$app_strings.LNK_LIST_PREVIOUS}
                </button>
                {else}
                <button type="button" class="button" title="{$app_strings.LNK_LIST_PREVIOUS}" disabled='true'>
                    {sugar_getimage name="previous_off" attr="border=\"0\" align=\"absmiddle\"" ext=".gif" alt=$app_strings.LNK_LIST_PREVIOUS}
                </button>
                {/if}
                &nbsp;&nbsp;
                ({$offset}{if !empty($total)} {$app_strings.LBL_LIST_OF} {$total}{$plus}{/if})
                &nbsp;&nbsp;
                {if !empty($next_link)}
                <button type="button" class="button" title="{$app_strings.LNK_LIST_NEXT}" onClick="document.location.href='{$next_link}';">
                    {sugar_getimage name="next" attr="border=\"0\" align=\"absmiddle\"" ext=".gif" alt=$app_strings.LNK_LIST_NEXT}
                </button>
                {else}
                <button type="button" class="button" title="{$app_strings.LNK_LIST_NEXT}" disabled="true">
                    {sugar_getimage name="next_off" attr="border=\"0\" align=\"absmiddle\"" ext=".gif" alt=$app_strings.LNK_LIST_NEXT}
                </button>
                {/if}
            </span>
            &nbsp;&nbsp;
        </td>
    </tr>
</table>
