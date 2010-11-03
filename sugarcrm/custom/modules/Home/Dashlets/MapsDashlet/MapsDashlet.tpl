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

// $Id: MapsDashlet.tpl,v 1.1 2006/10/11 00:53:31 clint Exp $

*}
<form name="EditView" method="POST" action="index.php">
<select id='maps_mapping_type_{$id}' onChange="selectDiv();"><option value="closest">Accounts within radius</option><option value="find">Map Account/Contact</option></select>&nbsp;<div id='maps_find_div_{$id}'><select id='maps_type_{$id}' onChange="changeQS();"><option value="Accounts">Accounts</option><option value="Contacts">Contacts</option></select>&nbsp;<input id='maps_input_{$id}' style='width: 25%; overflow: auto'  class="sqsEnabled" value='{$name}'><input id='maps_input_id_{$id}' type="hidden"><input id='maps_input_primary_address_street_{$id}' type="hidden"><input id='maps_input_primary_address_city_{$id}' type="hidden"><input id='maps_input_primary_address_state_{$id}' type="hidden"><input id='maps_input_primary_address_postalcode_{$id}' type="hidden"><input id='maps_input_primary_address_country_{$id}' type="hidden"><input id='maps_input_phone_work_{$id}' type="hidden">&nbsp;<input title='Select' tabindex='2' accessKey='Select' type='button' class='button' value='Select' id='maps_input_select_{$id}' name='maps_input_select_{$id}' onclick='Maps.openPopup();' /></div><div id='maps_closest_div_{$id}'>{$zipLbl}:&nbsp;<input id='maps_input_my_address_{$id}'>&nbsp;{$radiusLbl}:&nbsp;<select id='maps_input_my_dist_{$id}'><option value="5">5</option><option value="10">10</option><option value="15">15</option><option value="20">20</option></select></div>&nbsp;<input id='maps_submit_{$id}' type="button" class='button' onclick='Maps.click("{$id}")' value='Map'>
<div id='maps_output_{$id}' style='width: 100%; height: {$height}px; border: 1px #ddd solid'>{$mapsOutput}</div>
</form>
{literal}
<script>
selectDiv();
{/literal}{if $displayOnStartup == 'true'}{literal}
Maps.drawMap('{/literal}{$id}{literal}', true);
{/literal}{/if}{literal}
</script>{/literal}