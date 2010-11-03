{if $enableSeach == true}
{$QS}
{literal}<script>
if(typeof SLAcountdown == 'undefined') { // since the dashlet can be included multiple times a page, don't redefine these functions
	SLAcountdown = function() {
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
	        init: function() {
	        	registerSingleSmartInputListener(accountName = document.getElementById('account_name{/literal}{$id}{literal}')); //quicksearch
	        }
	    };
	}();
}

if(typeof YAHOO != 'undefined') YAHOO.util.Event.addListener(window, 'load', SLAcountdown.init);
</script>
{/literal}
{/if}
