//BEGIN SUGARCRM flav=sales ONLY
{if !$ISADMIN}
//END SUGARCRM flav=sales ONLY
<div class="dcmenuDivider" id="searchDivider"></div>
<div id="dcmenuSearchDiv">
        <div id="sugar_spot_search_div">
            <input size=20 id='sugar_spot_search'  title='enter global search term' onkeypress="DCMenu.startSearch(event);"/>
            <div id="sugar_spot_search_results" style="display: none;">
               
            </div>
        </div>
	<div id="glblSearchBtn">{$ICONSEARCH}
    </div>
</div>
//BEGIN SUGARCRM flav=sales ONLY
{/if}
//END SUGARCRM flav=sales ONLY
