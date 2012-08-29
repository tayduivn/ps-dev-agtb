{*
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Sales Subscription
 * License Agreement ("License") which can be viewed at
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
<div class="dcmenuDivider" id="globalLinksDivider"></div>
<div id="globalLinksModule">
    <ul class="clickMenu" id="globalLinks">
        <li>
            <ul class="subnav iefixed">
                {foreach from=$GCLS item=GCL name=gcl key=gcl_key}
    			    <li><a id="{$gcl_key}_link" href="{$GCL.URL}" {if $smarty.foreach.gcl.last}class="last"{/if}{if !empty($GCL.ONCLICK)} onclick="{$GCL.ONCLICK}"{/if}>{$GCL.LABEL}</a></li>

	                {foreach from=$GCL.SUBMENU item=GCL_SUBMENU name=gcl_submenu key=gcl_submenu_key}
	                    <a id="{$gcl_submenu_key}_link" href="{$GCL_SUBMENU.URL}"{if !empty($GCL_SUBMENU.ONCLICK)} onclick="{$GCL_SUBMENU.ONCLICK}"{/if}>{$GCL_SUBMENU.LABEL}</a>
	                {/foreach}
                {/foreach}

                <li><a id="logout_link" href='{$LOGOUT_LINK}' class='utilsLink'>{$LOGOUT_LABEL}</a> </li>
            </ul>
            <span> 
        	    <div id="dcmenuUserIcon" {$NOTIFCLASS}>
				  {$NOTIFICON}
				</div>
            	<a id="welcome_link" href='javascript: void(0);'>{$CURRENT_USER}</a>
            	
            </span>
        </li>
    </ul>
</div>

{if $NOTIFCODE != ""}
	<div class="dcmenuDivider" id="notifDivider"></div>
	<div id="dcmenuSugarCube" {$NOTIFCLASS} {if $ISADMIN}onclick="DCMenu.notificationsList();" title="{$APP.LBL_PENDING_NOTIFICATIONS}"{/if}>
	  {$NOTIFCODE}
	</div>
{else}
<div id="dcmenuSugarCubeEmpty"></div>
{/if}