//BEGIN SUGARCRM flav=sales ONLY
{if !$ISADMIN}
//END SUGARCRM flav=sales ONLY
<div class="dcmenuDivider" id="searchDivider"></div>
<div id="dcmenuSearchDiv">
        <div id="sugar_spot_search_div">
            <input size=20 id='sugar_spot_search'  title='Enter global search term' {if $ACTION  eq "spot" and $FULL eq "true"}style="display: none;"{/if}/>
            <img src="{sugar_getimagepath file="info-del.png"}" id="close_spot_search"/>
            <div id="sugar_spot_search_results" style="display: none;"></div>
            <div id="sugar_spot_ac_results"></div>
        </div>
	<div id="glblSearchBtn">{$ICONSEARCH}
    </div>
</div>
//BEGIN SUGARCRM flav=sales ONLY
{/if}
//END SUGARCRM flav=sales ONLY

{if $FTS_AUTOCOMPLETE_ENABLE}
{literal}
<script>
    var ftsAutoCompleteDs = new YAHOO.util.DataSource("index.php?", {
            responseType: YAHOO.util.XHRDataSource.TYPE_JSON,
            responseSchema: {resultsList: 'results'},connMethodPost: true}
        );
    var ftsAutoComplete = new YAHOO.widget.AutoComplete("sugar_spot_search", "sugar_spot_ac_results", ftsAutoCompleteDs, {
       generateRequest : function(sQuery)
       {
           var out = SUGAR.util.paramsToUrl({
               to_pdf: 'true',
               module: 'Home',
               action: 'quicksearchQuery',
               data: encodeURIComponent(YAHOO.lang.JSON.stringify({'method':'fts_query','conditions':[]})),
               query: sQuery
           });
           return out;
       }
   });
</script>
{/literal}
{/if}