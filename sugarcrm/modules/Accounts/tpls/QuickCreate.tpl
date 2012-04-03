{*
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
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
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
*}

{* -- Begin QuickCreate Specific -- *}
{if $smarty.request.action != 'SubpanelEdits'}
<input type="hidden" name="primary_address_street" value="{$smarty.request.primary_address_street}">
<input type="hidden" name="primary_address_city" value="{$smarty.request.primary_address_city}">
<input type="hidden" name="primary_address_state" value="{$smarty.request.primary_address_state}">
<input type="hidden" name="primary_address_country" value="{$smarty.request.primary_address_country}">
<input type="hidden" name="primary_address_postalcode" value="{$smarty.request.primary_address_postalcode}">
{/if}
<input type="hidden" name="is_ajax_call" value="1">
<input type="hidden" name="to_pdf" value="1">
{* -- End QuickCreate Specific -- *}
