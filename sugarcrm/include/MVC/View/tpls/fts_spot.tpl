{* //FILE SUGARCRM flav=pro ONLY *}
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
{if count($resultSet) > 0}
    {foreach from=$resultSet item=result name=searchresult}
    <section class="{if $smarty.foreach.searchresult.index  is even}even{else}odd{/if}">
        <div class="resultTitle">

        {$result->getModuleName()|upper}
 		</div>
 		{capture assign=url}index.php?module={$result->getModule()}&record={$result->getId()}&action=DetailView{/capture}
            <ul class='fts_spot_ul' >
                <li >
                    <span class="spot_fts_summary"><a href="{$url}">{$result->getSummaryText()}</a></span>
                    <br>
                    <span class="details">
                            {foreach from=$result->getHighlightedHitText() key=k item=v}
                                {$k}: {$v}
                            {/foreach}
                    </span>
                </li>
            </ul>
        <div class="clear"></div>
    </section>
    {/foreach}

    <p class="fullResults"><a href="index.php?module=Home&action=spot&full=true&q={$queryEncoded}">{$APP.LNK_SEARCH_FTS_VIEW_ALL}</a></p>
{elseif !isset($resultSet) }
    <section class="resultNull">
        <h1>{$APP.LBL_SEARCH_UNAVAILABLE}</h1>
   	</section>
{else}
	<section class="resultNull">
        <h1>{$APP.LBL_EMAIL_SEARCH_NO_RESULTS}</h1>
        <div style="float:right;">
            <a href="index.php?module=Home&action=spot&full=true&q={$queryEncoded}">{$APP.LNK_ADVANCED_SEARCH}</a>
        </div>
   	</section>
{/if}
