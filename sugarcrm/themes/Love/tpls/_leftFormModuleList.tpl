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
<div id="moduleList" class="leftList">
<ul class="shortcuts">
    <li class="TopBorder">
    	<span class="TopBorderLeft"></span>
    </li>
    {foreach from=$moduleTopMenu item=module key=name name=moduleList}
    {if $name == $MODULE_TAB}
	    <li class="currentShortcut">
	        {sugar_link id="moduleTab_$name" module=$name}
		</li>
		{if $name!='Home'}
		    {counter start=1 name="num" assign="num"}
		    {foreach from=$SHORTCUT_MENU item=item}
			    <li class="subshortcut">
			        <a href="{$item.URL}">{$item.IMAGE}&nbsp;
			        	<span>{$item.LABEL}</span>
			    	</a>
			    </li>
			    {counter name="num"}
		    {/foreach}
	    {/if}
	{else}
    <li class="notCurrentShortcut">
       {sugar_link id="moduleTab_$name" module=$name}
    {/if}
    </li>
    {/foreach}
    {if count($moduleExtraMenu) > 0}
        {foreach from=$moduleExtraMenu item=module key=name name=moduleList}
        {if $name == $MODULE_TAB}
		<li class="noBorder subshortcut">
		    {sugar_link id="moduleTab_$name" module=$name data=$module}
		{else}
		<li class="notCurrentShortcut">
		    {sugar_link id="moduleTab_$name" module=$name data=$module}
		{/if}
		</li>
        {/foreach}
        <li class="BottomBorder">
        <span class="BottomBorderLeft"></span>
    </li>
    
    {/if}
</ul>
</div>
