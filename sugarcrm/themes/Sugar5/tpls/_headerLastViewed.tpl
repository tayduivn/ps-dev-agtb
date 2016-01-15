{*
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
*}
<div id="lastView" class="headerList">
        <b style="white-space:nowrap;">{$APP.LBL_LAST_VIEWED}:&nbsp;&nbsp;</b>
    <span>
    {foreach from=$recentRecords item=item name=lastViewed}
    <span>
        
        <a title="{$item.item_summary}"
            accessKey="{$smarty.foreach.lastViewed.iteration}"
            href="{sugar_link module=$item.module_name action='DetailView' record=$item.item_id link_only=1}">
            {$item.image}&nbsp;<span>{$item.item_summary_short}</span>
        </a>
    </span>
    {foreachelse}
    {$APP.NTC_NO_ITEMS_DISPLAY}
    {/foreach}
    </span>
</div>
