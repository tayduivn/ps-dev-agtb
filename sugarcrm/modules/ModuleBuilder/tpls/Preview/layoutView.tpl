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

<table cellspacing='2'>
	<tr>
	{$buttons}
	</tr>
</table>
<div style='width:675px;' class='preview'>
<div style='position: relative; left:245px; top:45px; float:left' id='layoutPreview'>
<h3>{$layouttitle}</h3>
{foreach from=$layout item='panel' key='panelid'}
	<div class='le_panel'>
        <div class='panel_label' id='le_panellabel_{$idCount}'>
          <span class='panel_name' id='le_panelname_{$idCount}'>{if !empty($translate)}{sugar_translate label=$panelid|upper module=$language}{else}{$panelid}{/if}</span>
          <span class='panel_id' id='le_panelid_{$idCount}'>{$panelid}</span>
        </div>
		{counter name='idCount' assign='idCount' print=false}
			
		{foreach from=$panel item='row' key='rid'}
			<div class='le_row'>
			{counter name='idCount' assign='idCount' print=false}	
			{foreach from=$row item='col' key='cid'}
				{if $col.name != "(empty)"}
				{assign var='nextcid' value=`$cid+1`}
				<div class='le_field' {if $cid == 0 && $row.$nextcid.name == "(empty)"}style="width:290px"{/if}> 
					{if isset($col.type) && ($col.type == 'address')}
						{$icon_address}
					{/if}
					{if isset($col.type) && ($col.type == 'phone')}
						{$icon_phone}
					{/if}
					<span >{if !empty($translate) && !empty($col.label)}
						{eval var=$col.label assign='newLabel'}
						{sugar_translate label=$newLabel module=$language}
					{else}
						{$col.label}
					{/if}</span>
					<span class='field_name'>{$col.name}</span>
					<span class='field_label'>{$col.label}</span>
					<span class='field_tabindex'>{$col.tabindex}</span>
				</div>
				{/if}
				{counter name='idCount' assign='idCount' print=false}
			{/foreach}
		</div>	
	{/foreach}
	</div>
{/foreach}
</div></div>