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

</div>

{if $FTS_AUTOCOMPLETE_ENABLE}
{literal}
<script>

    var data = encodeURIComponent(YAHOO.lang.JSON.stringify({'method':'fts_query','conditions':[]}));
    $( "#sugar_spot_search" ).autocomplete({
    			source: 'index.php?to_pdf=true&module=Home&action=quicksearchQuery&data='+data,
                select: function(event, ui) {
                    DCMenu.spot(ui.item.value);
                }
    		}).data( "autocomplete" )._renderItem = function( ul, item )
    		                {
                                return $( "<li></li>" )
                                				.data( "item.autocomplete", item )
                                				.append('<a>' + item.label + '</a>')
                                				.appendTo( ul );
    		                };
</script>
{/literal}
{/if}