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
<div id="moduleList">
<ul>
    <li class="noBorder">&nbsp;</li>
    {foreach from=$moduleTopMenu item=module key=name name=moduleList}
    {if $name == $MODULE_TAB}
    <li class="noBorder">
        <span class="currentTabLeft">&nbsp;</span><span class="currentTab">{sugar_link id="moduleTab_$name" module=$name}&nbsp;{sugar_getimage name="advanced_search" ext=".gif" other_attributes='id="moduleTabMenu_$name" '}</span><span class="currentTabRight">&nbsp;</span>
    {else}
    <li>
        <span class="notCurrentTabLeft">&nbsp;</span><span class="notCurrentTab">{sugar_link id="moduleTab_$name" module=$name}&nbsp;{sugar_getimage name="advanced_search" ext=".gif" other_attributes='id="moduleTabMenu_$name" '}</span><span class="notCurrentTabRight">&nbsp;</span>
    {/if}
    </li>
    {/foreach}
    {if count($moduleExtraMenu) > 0}
    <li id="moduleTabExtraMenu">
        <a href="#">&nbsp;&nbsp;</a>
        <ul id="cssmenu" class="cssmenu">
            {foreach from=$moduleExtraMenu item=module key=name name=moduleList}
            <li>{sugar_link id="moduleTab_$name" module=$name}
            {/foreach}
        </ul>        
    </li>
    {/if}
</ul>
</div>
