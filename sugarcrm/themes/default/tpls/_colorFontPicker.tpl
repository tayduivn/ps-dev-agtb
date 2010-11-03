{*
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise Subscription
 * Agreement ("License") which can be viewed at
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
 * by SugarCRM are Copyright (C) 2004-2010 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
*}
{if $AUTHENTICATED}
<div id="colorPicker">
    <span>|</span>
    <span id="themepickerLinkSpan">
        {$APP.LBL_THEME_PICKER}
    </span>
</div>

<div id="themepickerDialog" style="display:none;">
    <div class="hd">
        {$APP.LBL_THEME_PICKER}
    </div>	
    <div id="stylepicker" class="edit view">
        <form id="themepickerDialogForm" name="themepickerDialogForm" method="POST"
                class='themePicker' action="{$smarty.server.REQUEST_URI}">
            <table width='100%' border='0' cellpadding='0' cellspacing='1'>
            <tr>
                <td scope="row">{$APP.LBL_THEME}</td>
                <td>
                    <select name="usertheme" id="usertheme">
                    {$selectThemeOptions}
                    </select>
                </td>
           </tr>
            {if $currentTheme_groupedTabscompat}
            {capture name=SMARTY_NAVIGATION_PARADIGM_DESC}&nbsp;{$USERS_MOD.LBL_NAVIGATION_PARADIGM_DESCRIPTION}<i>{$USERS_MOD.LBL_SUPPORTED_THEME_ONLY}</i>{/capture}
            <tr>
                <td scope="row">{$USERS_MOD.LBL_NAVIGATION_PARADIGM}:</td>
                <td>
                    <select name="userthemegrouptabs" id='userthemegrouptabs'>
                        {$NAVADIGMS}
                    </select>
                </td>
            </tr>
            {/if}
            {if count($currentTheme_colors) > 1 || count($currentTheme_fonts) > 1}
            <tr>
            <td colspan="2"><hr /></td>
            </tr>
            {/if}
           {if count($currentTheme_colors) > 1}
           <tr>
                <td colspan="2">
                    <input type="hidden" name="usercolor" id="usercolor" value="">
                    <ul id="color_menu">
                    {foreach from=$currentTheme_colors item=color name=theme_colors}
                    {capture name=themeColorIcon assign=colorIcon}colors.{$color}.icon.gif{/capture}
                    <li style="background: url({sugar_getimagepath file=$colorIcon}) no-repeat center;" onclick="SUGAR.themes.changeColor('{$color}');"></li>
                    {/foreach}
                    </ul>
                </td>
           </tr>
           {/if}
           {if count($currentTheme_fonts) > 1}
           <tr>
                <td colspan="2">
                    <input type="hidden" name="userfont" id="userfont" value="">
                    <ul id="font_menu">
                    {foreach from=$currentTheme_fonts item=font name=theme_fonts}
                    {capture name=themeFontIcon assign=fontIcon}fonts.{$font}.icon.gif{/capture}
                    <li style="background: url({sugar_getimagepath file=$fontIcon}) no-repeat center;" onclick="SUGAR.themes.changeFont('{$font}');"></li>
                    {/foreach}
                    </ul>
                </td>
            </tr>
            {/if}
            </table>
        </form>
    </div>
</div>
{/if}
