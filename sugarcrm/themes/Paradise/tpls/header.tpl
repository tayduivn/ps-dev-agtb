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
{include file="_head.tpl" theme_template=true}
<body onMouseOut="closeMenus();">

<div id="HideMenu" class="leftList">
{if $AUTHENTICATED}
{include file="_leftFormHiddenLastViewed.tpl" theme_template=true}
{include file="_leftFormHiddenShortcuts.tpl" theme_template=true}
{/if}
</div>

<div id="header">
    {include file="_companyLogo.tpl" theme_template=true}
    <div id="island"></div>
    {include file="_colorFontPicker.tpl" theme_template=true}
    {include file="_globalLinks.tpl" theme_template=true}
    <div class="clear"></div>
    {if !$AUTHENTICATED}
    <br /><br />
    <div class="clear"></div>
    {/if}
    
    {if $USE_GROUP_TABS}
    {include file="_headerModuleListGroupTabs.tpl" theme_template=true}
    {else}
    {include file="_headerModuleList.tpl" theme_template=true}
    {/if}
    <div id="top_welcomesearch" class="clear"></div>
    <div id="welcomeSearch">
	    	{include file="_headerSearchAlt.tpl" theme_template=true}
			{include file="_welcome.tpl" theme_template=true}
		</div>
<div class="clear"></div>
		{if $AUTHENTICATED}
	    {include file="_headerLastViewed.tpl" theme_template=true}
	    {include file="_headerShortcuts.tpl" theme_template=true}
	    {/if}
</div>
	
<div id="main">
    {if $AUTHENTICATED}
    {include file="_leftFormHide.tpl" theme_template=true}
    <div id="leftColumn">
        {include file="_leftFormLastViewed.tpl" theme_template=true}
        {include file="_leftFormShortcuts.tpl" theme_template=true}
        {include file="_leftFormNewRecord.tpl" theme_template=true}
    </div>
    {/if}
    <div id="content" {if !$AUTHENTICATED}class="noLeftColumn" {/if}>
        <table><tr><td>
