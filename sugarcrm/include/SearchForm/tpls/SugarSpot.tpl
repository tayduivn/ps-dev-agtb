{*
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
*}

<div id='SpotResults'>
{if !empty($displayResults)}
    {foreach from=$displayResults key=module item=data}
    <section>
        <div class="resultTitle">
            {if isset($appListStrings.moduleList[$module])}
                {$appListStrings.moduleList[$module]}
            {else}
                {$module}
            {/if}
            {if !empty($displayMoreForModule[$module])}
                {assign var="more" value=$displayMoreForModule[$module]}
                <br>
                <small class='more' onclick="DCMenu.spotZoom('{$more.query}', '{$module}', '{$more.offset}');">({$more.countRemaining} {$appStrings.LBL_SEARCH_MORE})</small>
            {/if}
        </div>
            <ul>
                {foreach from=$data key=id item=name}
                        <div onmouseover="DCMenu.showQuickViewIcon('{$id}')" onmouseout="DCMenu.hideQuickViewIcon('{$id}')" class="gs_div" style="position: relative" >
                            <div id="gs_div_{$id}" style="position: absolute;left: 0" class="SpanQuickView">
                                    <img id="gs_img_{$id}" class="QuickView" src="themes/default/images/Search.gif" alt="quick_view_{$id}" onclick="DCMenu.showQuickView('{$module}', '{$id}');return false;">

                            </div>

                        <div class="gsLinkWrapper" >
                            <a href="index.php?module={$module}&action=DetailView&record={$id}" class="gs_link">{$name}</a>
                        </div>
                        </div>
                {/foreach}
            </ul>
        <div class="clear"></div>
    </section>
    {/foreach}
    <a href='index.php?module=Home&action=UnifiedSearch&search_form=false&advanced=false&query_string={$queryEncoded}' class="resultAll">
        {$appStrings.LNK_SEARCH_NONFTS_VIEW_ALL}
    </a>
{else}
    <section class="resultNull">
        <h1>{$appStrings.LBL_EMAIL_SEARCH_NO_RESULTS}</h1>
        <div style="float:right;">
            <a href="index.php?module=Home&action=UnifiedSearch&search_form=false&advanced=false&query_string={$queryEncoded}">{$appStrings.LNK_ADVANCED_SEARCH}</a>
        </div>
    </section>
{/if}
</div>
