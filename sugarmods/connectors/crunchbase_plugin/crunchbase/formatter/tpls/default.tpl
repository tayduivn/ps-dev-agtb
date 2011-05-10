<div id="crunchbase_popup_div"></div>
<script type="text/javascript">
{literal}
function show_ext_rest_crunchbase(event) {

        var xCoordinate = event.clientX;
        var yCoordinate = event.clientY;
        var isIE = document.all?true:false;
        
		if(isIE) {
		    xCoordinate = xCoordinate + document.body.scrollLeft;
		    yCoordinate = yCoordinate + document.body.scrollTop;
		}
        
        
		var callback =	{
			success: function(data) {
				if(data.responseText != ''){
					eval('result = ' + data.responseText);
					if(data.responseText != '' && result) {
					    body_limit = {{$config.properties.popup_body_limit}};
					    if(result['overview'].length > body_limit) {
					       result['overview'] = result['overview'].substr(0,body_limit-3) + '...';
					    }

						cd = new CompanyDetailsDialog("crunchbase_popup_div", result['overview'], data.argument.x, data.argument.y);
						cd.setHeader(result['name']);
						cd.setFooter('<a target=\"crunchbase_window\" href=\"' + result['crunchbase_url'] + '\">' + result['crunchbase_url'] + '</a>');
						cd.display();
						return;
					}else{
						alert(SUGAR.language.get('app_strings','ERROR_UNABLE_TO_RETRIEVE_DATA'));
					}
				}else{
					alert(SUGAR.language.get('app_strings','ERROR_UNABLE_TO_RETRIEVE_DATA'));
				}
			},
			failure: function(data) {
				alert(SUGAR.language.get('app_strings','ERROR_UNABLE_TO_RETRIEVE_DATA'));
			},
			argument: {y: yCoordinate, x: xCoordinate}	  
		}

{/literal}

		crunchbase_url = '{{$config.properties.company_url}}';
		company = '{$fields.{{$company_name_mapping}}.value}';
		
{literal}
		if(company != '') {
		    company = company.trim().toLowerCase().replace(/ /g, '-');
		    company = company.replace(/\.+$/,''); //Remove any trailing periods
			crunchbase_url = crunchbase_url + company + '.js';
			url = 'index.php?module=Connectors&action=CallRest&url=' + crunchbase_url;
			var cObj = YAHOO.util.Connect.asyncRequest('GET', url, callback, null);   
		}
}
{/literal}
</script>