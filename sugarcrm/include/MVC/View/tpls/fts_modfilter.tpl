{* //FILE SUGARCRM flav=pro ONLY *}
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

*}

<div class="ftsModuleFilterSpan">
    {if empty($smarty.request.m)}
        {if !empty($smarty.request.append_wildcard)}
            <input type="checkbox" checked="checked" id="all" name="module_filter" class="ftsModuleFilter" disabled="disabled">
        {else}
            <input type="checkbox" checked="checked" id="all" name="module_filter" class="ftsModuleFilter">
        {/if}
        <span id="all_label" class="checked">&nbsp;{$APP.LBL_EMAIL_SHOW_READ}</span>
    {else}
        {if !empty($smarty.request.append_wildcard)}
            <input type="checkbox" id="all" name="module_filter" class="ftsModuleFilter" disabled="disabled">
        {else}
            <input type="checkbox" id="all" name="module_filter" class="ftsModuleFilter">
        {/if}
        <span id="all_label" class="unchecked">&nbsp;{$APP.LBL_EMAIL_SHOW_READ}</span>
    {/if}
</div>
{foreach from=$filterModules item=entry key=module}
    <div class="ftsModuleFilterSpan">
        {if is_array($smarty.request.m) && in_array($entry.module, $smarty.request.m)}
            {if !empty($smarty.request.append_wildcard)}
                <input type="checkbox" checked="checked" id="{$entry.module}" name="module_filter" class="ftsModuleFilter" disabled="disabled">
            {else}
                <input type="checkbox" checked="checked" id="{$entry.module}" name="module_filter" class="ftsModuleFilter">
            {/if}
            <span id="{$entry.module}_label" class="checked">&nbsp;{$entry.label}</span>
            <span id="{$entry.module}_count" class="checked">{if is_int($entry.count)}({$entry.count}){/if}</span>
        {else}
            {if !empty($smarty.request.append_wildcard)}
                <input type="checkbox" id="{$entry.module}" name="module_filter" class="ftsModuleFilter" disabled="disabled">
            {else}
                <input type="checkbox" id="{$entry.module}" name="module_filter" class="ftsModuleFilter">
            {/if}
            <span id="{$entry.module}_label" class="unchecked">&nbsp;{$entry.label}</span>
            <span id="{$entry.module}_count" class="unchecked">{if is_int($entry.count) }({$entry.count}){/if}</span>
        {/if}
    </div>
{/foreach}