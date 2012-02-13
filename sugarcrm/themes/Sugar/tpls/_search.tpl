//BEGIN SUGARCRM flav=sales ONLY
{if !$ISADMIN}
//END SUGARCRM flav=sales ONLY
<div class="dcmenuDivider" id="searchDivider"></div>
<div id="dcmenuSearchDiv">
        <div id="sugar_spot_search_div">
            <input size=20 id='sugar_spot_search'  title='Enter global search term' {if $ACTION  eq "spot" and $FULL eq "true"}style="display: none;"{/if}/>
            <img src="{sugar_getimagepath file="info-del.png"}" id="close_spot_search"/>
            <div id="sugar_spot_search_results" style="display:none;"></div>
            <div id="sugar_spot_ac_results"></div>
        </div>
	<div id="glblSearchBtn">{$ICONSEARCH}
    </div>
</div>
//BEGIN SUGARCRM flav=sales ONLY
{/if}
//END SUGARCRM flav=sales ONLY

</div>

{if $FTS_AUTOCOMPLETE_ENABLE}
{literal}
<script>

    var data = encodeURIComponent(YAHOO.lang.JSON.stringify({'method':'fts_query','conditions':[]}));
    var autoCom = $( "#sugar_spot_search" ).autocomplete({
        source: 'index.php?to_pdf=true&module=Home&action=quicksearchQuery&data='+data,
        minLength: 3,
        select: function(event, ui) {},
        search: function(event,ui){
        var el = $("#sugar_spot_search_results");
                   if ( !el.is(":visible") ) {
                       el.html('');
                       el.show();
                   }
            $('#sugar_spot_search_results').showLoading();
        }
    	}).data( "autocomplete" )._response = function(content)
        {
            var el = $("#sugar_spot_search_results");
            if ( !el.is(":visible") ) {
                el.show();
            }
            if(typeof(content.results) != 'undefined'){
                el.html( content.results);
            }
            this.pending--;

            $('#sugar_spot_search_results').hideLoading();
        };
</script>
{/literal}
{/if}