//BEGIN SUGARCRM flav=sales ONLY
{if !$ISADMIN}
//END SUGARCRM flav=sales ONLY
<div class="dcmenuDivider" id="searchDivider"></div>
<div id="dcmenuSearchDiv">
        <div id="sugar_spot_search_div">
            <input size=20 id='sugar_spot_search'  title='enter global search term'/>
            <div id="results" style="display: none;">
                <section>
                    <div class="resultTitle">Top hit</div>
                    <ul>
                        <li><a href="">Anytime Air Support Inc - 1000 units </a></li>
                        <li><a href="">Orville Yuen</a></li>
                    </ul>
                <div class="clear"></div>
                </section>
                <section>
                    <div class="resultTitle">Favorites</div>
                    <ul>
                        <li><a href="">Nettie Tanguay</a></li>
                    </ul>
                <div class="clear"></div>
                </section>
                <section>
                    <div class="resultTitle">Contacts</div>
                    <ul>
                        <li><a href="">Dena Staggs</a></li>
                        <li><a href="">Saul Wash</a></li>
                        <li><a href="">Alexis Tylor</a></li>
                    </ul>
                <div class="clear"></div>
                </section>
                <a href="" class="resultAll">View all results</a>

                <section class="resultNull">
                    <h1>No results found</h1>
                    <a href="">Search again</a>
                </section>
            </div>
        </div>
	<div id="glblSearchBtn">{$ICONSEARCH}
    </div>
</div>
//BEGIN SUGARCRM flav=sales ONLY
{/if}
//END SUGARCRM flav=sales ONLY
