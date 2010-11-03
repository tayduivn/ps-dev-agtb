
{*

/*********************************************************************************

 * The contents of this file are subject to the SugarCRM Enterprise Subscription
 * Agreement ("License") which can be viewed at
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
 * by SugarCRM are Copyright (C) 2004-2007 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

*}

<hr />

<!-- ListView Data -->

{if $SUBPANEL_LIST_VIEW}

	<div class="sectitle">

	{$BEAN->name} <small>[ <a class="back_link" href="index.php?module={$MODULE}&action=wirelessdetail&record={$BEAN->id}">{sugar_translate label='LBL_BACK' module=''}</a> ]</small>

	</div>

	<div class="subpanel_sec">

	{sugar_translate label='LBL_RELATED' module=''} {$SUBPANEL_MODULE}<br />

	</div>

	<ul class="sec">

	{foreach from=$DATA item="record" name="recordlist"}

	<li class="{if $smarty.foreach.recordlist.index % 2 == 0}odd{else}even{/if}">

        <a href="index.php?module={$record->module_dir}&action=wirelessdetail&record={$record->id}">{$record->name}</a>

    </li>

	{/foreach}

	</ul>

{else}

	<div class="sectitle">{sugar_translate label='LBL_SEARCH_RESULTS' module=''}{if $SAVED_SEARCH_NAME} - {$SAVED_SEARCH_NAME}{/if}</div>

	<ul class="sec">

	{foreach from=$DATA item="record" name="recordlist"}

	<li class="{if $smarty.foreach.recordlist.index % 2 == 0}odd{else}even{/if}">

         {if $MODULE == "Cases"}
            <a href="index.php?module={$MODULE}&action=wirelessdetail&record={$record.ID}">{$record.CASE_NUMBER}&nbsp;&nbsp;{$record.NAME}</a>
        {else}
            <a href="index.php?module={$MODULE}&action=wirelessdetail&record={$record.ID}">{$record.NAME}</a>
        {/if}
		
    </li>

	{/foreach}

	</ul>

	<div class="nav_sec" align="right">

	{if $PAGEDATA.offsets.prev != -1}<small><a href={$PAGEDATA.urls.prevPage} class="nav">{$navStrings.previous}</a>&nbsp;</small>{/if}

	{if $PAGEDATA.offsets.lastOffsetOnPage == 0}0{else}{$PAGEDATA.offsets.current+1}{/if} - {$PAGEDATA.offsets.lastOffsetOnPage} {$navStrings.of} {if $PAGEDATA.offsets.totalCounted}{$PAGEDATA.offsets.total}{else}{$PAGEDATA.offsets.total}{if $PAGEDATA.offsets.lastOffsetOnPage != $PAGEDATA.offsets.total}+{/if}{/if}

	{if $PAGEDATA.offsets.next != -1}<small>&nbsp;<a href={$PAGEDATA.urls.nextPage} class="nav">{$navStrings.next}</a></small>{/if}

	</div>

	<div class="sectitle">{sugar_translate label='LBL_SEARCH' module=''} {$MODULE} Module</div>

	{$WL_SAVED_SEARCH_FORM}

	<!--  Search Def Searches -->

	{$WL_SEARCH_FORM}	

{/if}

