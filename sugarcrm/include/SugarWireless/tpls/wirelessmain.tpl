
{*
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
*}
<hr />
<!-- Activities for the day -->
{if $todays_activities}
<div class="sectitle">{sugar_translate label='LBL_TODAYS_ACTIVITIES' module=''}</div>
{foreach from=$activities_today item=data key=module}
	<div class="subpanel_sec">{$module}:</div>
	<ul class="sec">
	{foreach from=$data item=activity name="activitylist"}
	{assign var="activity_image" value=$module}
	{assign var="dotgif" value=".gif"}	
	<li class="{if $smarty.foreach.activitylist.index % 2 == 0}odd{else}even{/if}">
        <a href=index.php?module={$module}&action=wirelessdetail&record={$activity.ID}>{sugar_getimage name=$activity_image$dotgif alt=$activity_image other_attributes='border="0" '}&nbsp;
        {$activity.NAME}</a>
    </li>
	{/foreach}
	</ul>
{/foreach}
{/if}
<!-- Last Viewed -->
{if $last_viewed}
<div class="sectitle">{$LBL_LAST_VIEWED}</div>
<ul class="sec">
	{foreach from=$LAST_VIEWED_LIST item=LAST_VIEWED key=ID name="recordlist"}
	{assign var="module_image" value=$LAST_VIEWED.module}
	{assign var="dotgif" value=".gif"}
	<li class="{if $smarty.foreach.recordlist.index % 2 == 0}odd{else}even{/if}">
        <a href=index.php?module={$LAST_VIEWED.module}&action=wirelessdetail&record={$ID}>{sugar_getimage name=$module_image$dotgif alt=$module_image other_attributes='border="0" '}&nbsp;
        {$LAST_VIEWED.summary}</a>
    </li>
	{/foreach}
</ul>
{/if}