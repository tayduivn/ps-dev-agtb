{*
//FILE SUGARCRM flav=int ONLY
*}

{literal}<script>
if(typeof GoogleGadgets == 'undefined') { // since the dashlet can be included multiple times a page, don't redefine these functions
	GoogleGadgets = function() {
	    return {
	    	/**
	    	 * Called when the textarea is blurred
	    	 */
	        changedCategory: function(id) {
	        	var category = document.getElementById('category_' + id);
	        	var gadgets = document.getElementById('gadget_' + id);
				gadgets.options.length = 0;
				var cat = category.options[category.selectedIndex].value;
				for( g in GoogleGadgets.gadgets[cat]){
					gadgets.options[gadgets.options.length] = new Option(g, g, false, false)
				}
	        },
		    /**
	    	 * Called when the textarea is double clicked on
	    	 */
			load: function(id) {
				var category = document.getElementById('category_' + id);
				category.options.length = 0;
				for( cat in GoogleGadgets.gadgets){
					category.options[category.options.length] = new Option(cat, cat, false, false)
				}
				GoogleGadgets.changedCategory(id);
			}
		    
	       
	    };
	}();
}
{/literal}
GoogleGadgets.gadgets = {$gadgets};
GoogleGadgets.defaultCategory = '{$cat}';
GoogleGadgets.defaultGadget = '{$gadget}';
</script>
