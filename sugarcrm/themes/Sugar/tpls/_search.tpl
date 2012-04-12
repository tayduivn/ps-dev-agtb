//BEGIN SUGARCRM flav=sales ONLY
{if !$ISADMIN}
//END SUGARCRM flav=sales ONLY
<div class="dcmenuDivider" id="searchDivider"></div>
<div id="dcmenuSearchDiv">
        <div id="sugar_spot_search_div">
            <div id="spot_search_btn" onclick="DCMenu.spot(document.getElementById('sugar_spot_search').value);">&nbsp;</div>
            <input size=20 id='sugar_spot_search'  title='Enter global search term' {if $ACTION  eq "spot" and $FULL eq "true"}style="display: none;"{/if}/>
            <img src="{sugar_getimagepath file="info-del.png"}" id="close_spot_search"/>
            <div id="sugar_spot_search_results" style="display:none;">
                {if $FTS_AUTOCOMPLETE_ENABLE}
                <div align="right">
                    <p class="fullResults"><a href="index.php?module=Home&action=spot&full=true">{$APP.LNK_ADVANCED_SEARCH}</a></p>
                </div>
                {/if}
            </div>

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
        source: 'index.php?to_pdf=true&module=Home&action=quicksearchQuery&append_wildcard=true&data='+data,
        minLength: 3,
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


    $( "#sugar_spot_search" ).bind( "focus.autocomplete", function() {

        //If theres old data, clear it.
          if( $("#sugar_spot_search_results").find('section').length > 0 )
              $("#sugar_spot_search_results").html('');
		$('#sugar_spot_search_div').css("width",360);
		$('#sugar_spot_search').css("width",290);
        $("#sugar_spot_search_results").show();
    });


</script>
{/literal}
{/if}