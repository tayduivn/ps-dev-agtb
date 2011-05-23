{*<input type='text' name='tags_search' size='50' value='{sugarvar objectName=$parentFieldArray memberName=$vardef.name key='value'}'>*}

<input type='text' name='tags_search' id='tags_search'
	value='{$smarty.request.tags_search}'>

{literal}
<script>
if(typeof(SUGAR.TagFields) == 'undefined') SUGAR.TagFields = {};
if(typeof(SUGAR.TagFields._availableTags) == 'undefined'){
	SUGAR.TagFields._availableTags = [
		{/literal}
		{{$all_tags_str}}
		{literal}
	];
}
YUI().use("autocomplete", "autocomplete-filters", "autocomplete-highlighters", function (Y) {
  var inputNode = Y.one('#tags_search'),
      tags = [
		{/literal}
		{{$all_tags_str}}
		{literal}
      ];
 
    inputNode.plug(Y.Plugin.AutoComplete, {
      activateFirstItem: true,
      allowTrailingDelimiter: true,
      minQueryLength: 0,
      queryDelay: 0,
      queryDelimiter: ',',
      source: tags,
      resultHighlighter: 'startsWith',
      zIndex: 99999,
 
      // Chain together a startsWith filter followed by a custom result filter
      // that only displays tags that haven't already been selected.
      resultFilters: ['startsWith', function (query, results) {
        // Split the current input value into an array based on comma delimiters.
        var selected = inputNode.ac.get('value').split(/\s*,\s*/);
 
        // Pop the last item off the array, since it represents the current query
        // and we don't want to filter it out.
        selected.pop();
 
        // Convert the array into a hash for faster lookups.
        selected = Y.Array.hash(selected);
 
        // Filter out any results that are already selected, then return the
        // array of filtered results.
        return Y.Array.filter(results, function (result) {
          return !selected.hasOwnProperty(result.text);
        });
      }]
    });
 
    // When the input node receives focus, send an empty query to display the full
    // list of tag suggestions.
    inputNode.on('focus', function () {
      inputNode.ac.sendRequest('');
    });
 	
	inputNode.on('blur', function(){
		var curr_array = inputNode.get('value').split(", ");
		var valid = true;
		for(x in curr_array){
			if(curr_array[x] != ""){
				var this_value = false;
				for(y in SUGAR.TagFields._availableTags){
					if(curr_array[x] == SUGAR.TagFields._availableTags[y]){
						this_value = true;
					}
				}
				if(this_value == false){
					valid = false;
					curr_array.splice(x, 1);
				}
			}
		}
		
		if(!valid){
			var fixed_str = curr_array.join(", ");
			if(fixed_str != ""){
				fixed_str = fixed_str + ", ";
			}
			inputNode.set('value', fixed_str);
		}
	});
	
    inputNode.ac.before('select', function () {
      inputNode._backup_value = inputNode.get('value');
    });
	
    // After a tag is selected, send an empty query to update the list of tags.
    inputNode.ac.after('select', function () {
      inputNode.ac.sendRequest('');
      inputNode.ac.show();
    });
});
</script>
{/literal}
