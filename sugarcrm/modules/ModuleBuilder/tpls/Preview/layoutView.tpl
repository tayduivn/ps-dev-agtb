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

<table id='layoutEditorButtons' cellspacing='2'>
    <tr>
    {$buttons}
    </tr>
</table>
<div id='layoutEditor' style="width:675px;">

<div id='toolbox' style='display:none';>
</div>

<div id='panels' style='float:left; overflow-y:auto; overflow-x:hidden'>

<h3>{$layouttitle}</h3>
{foreach from=$layout item='panel' key='panelid'}

    <div class='le_panel' id='{$idCount}'>

        <div class='panel_label' id='le_panellabel_{$idCount}'>
          <span class='panel_name' id='le_panelname_{$idCount}'>
          	{capture name=panel_upper assign=panel_upper}{$panelid|upper}{/capture}
			{if $panelid eq 'default'}
          		{$mod.LBL_DEFAULT}
			{elseif $from_mb && isset($current_mod_strings.$panel_upper)}
                {$current_mod_strings.$panel_upper}
			{elseif !empty($translate)}
			    {sugar_translate label=$panelid|upper module=$language}
			{else}
			    {$panelid}
			{/if}</span>
          <span class='panel_id' id='le_panelid_{$idCount}'>{$panelid}</span>
          {* //BEGIN SUGARCRM flav=een ONLY *}
          <span class='panel_id' id='le_paneldep_{$idCount}'>{$dependencies.$panelid}</span>
          {* //END SUGARCRM flav=een ONLY *}
        </div>
        {if $panelid ne 'default'}
 
        {/if}
        {counter name='idCount' assign='idCount' print=false}

        {foreach from=$panel item='row' key='rid'}
            <div class='le_row' id='{$idCount}'>
            {counter name='idCount' assign='idCount' print=false}

            {foreach from=$row item='col' key='cid'}
            {assign var="field" value=$col.name}
                <div class='le_field' id='{$idCount}'>
                    {if ! $fromModuleBuilder && ($col.name != '(filler)')}
                    {/if}
                    {if isset($col.type) && ($col.type == 'address')}
                        {$icon_address}
                    {/if}
                    {if isset($col.type) && ($col.type == 'phone')}
                        {$icon_phone}
                    {/if}
                    {* BEGIN SUGARCRM flav=pro ONLY *}
                    {if isset($field_defs.$field.calculated) && $field_defs.$field.calculated}
                        <img src="{sugar_getimagepath file='SugarLogic/icon_calculated.png'}" class="right_icon" />
                    {/if}
                    {if isset($field_defs.$field.dependency) && $field_defs.$field.dependency}
                        <img src="{sugar_getimagepath file='SugarLogic/icon_dependent.png'}" class="right_icon" />
                    {/if}
                    {* END SUGARCRM flav=pro ONLY *}
                    <span id='le_label_{$idCount}'>
                    {eval var=$col.label assign='label'}
                    {if !empty($translate) && !empty($col.label)}
                        {sugar_translate label=$label module=$language}
                    {else}
		                {if !empty($current_mod_strings[$label])}
		                    {$current_mod_strings[$label]}
		                {elseif !empty($mod[$label])}
		                    {$mod[$label]}
		                {else}
		                	{$label}
		                {/if}
		            {/if}</span>
                    <span class='field_name'>{$col.name}</span>
                    <span class='field_label'>{$col.label}</span>
                    <span id='le_tabindex_{$idCount}' class='field_tabindex'>{$col.tabindex}</span>
                </div>
                {counter name='idCount' assign='idCount' print=false}
            {/foreach}

        </div>
    {/foreach}

    </div>
{/foreach}

</div>
<input type='hidden' id='idCount' value='{$idCount}'>
</div>