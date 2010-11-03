{$QS}
{literal}<script>
if(typeof CasePerformance == 'undefined') { // since the dashlet can be included multiple times a page, don't redefine these functions
	CasePerformance = function() {
	    return {
	    	/**
	    	 * Called onSubmit, to search for account or case number
	    	 */
	        search: function(id) {
	        	caseNumber =  document.getElementById('case_number').value;
	        	accountName = document.getElementById('account_name{/literal}{$id}{literal}').value;
	        	url = "index.php?to_pdf=1&module=Home&action=DynamicAction&DynamicAction=displayDashlet&sugar_body_only=1&id=" + id + "&caseNumber=" + caseNumber + "&accountName=" + accountName;
	        	
			return SUGAR.mySugar.retrieveDashlet(id, url);
			
	        },
	        doClear: function(id) {
	        	url = "index.php?to_pdf=1&module=Home&action=DynamicAction&DynamicAction=displayDashlet&sugar_body_only=1&id=" + id + "";
	        	
			return SUGAR.mySugar.retrieveDashlet(id, url);
			
	        },
	        doExport: function(id) {
	        	caseNumber =  document.getElementById('case_number').value;
	        	if(caseNumber == "") {
	        		caseNumber = document.getElementById('case_number_hidden').value;
	        	}
	        	accountName = document.getElementById('account_name{/literal}{$id}{literal}').value;
	        	if(accountName == "") {
	        		accountName = document.getElementById('account_hidden{/literal}{$id}{literal}').value;
	        	}
		        url = "index.php?to_pdf=1&module=Home&action=DynamicAction&DynamicAction=displayDashlet&doExport=true&sugar_body_only=1&id=" + id + "&caseNumber=" + caseNumber + "&accountName=" + accountName;
	        	window.open(url,'exportWindow');
	        },
	        init: function() {
	        	registerSingleSmartInputListener(accountName = document.getElementById('account_name{/literal}{$id}{literal}')); //quicksearch
	        }
	    };
	}();
}
if(typeof YAHOO != 'undefined') YAHOO.util.Event.addListener(window, 'load', CasePerformance.init);


</script>{/literal}
