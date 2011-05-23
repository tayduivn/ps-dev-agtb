{multienum_to_array string={{sugarvar key='value' string=true}} assign="vals"}

{{capture name=idname assign=idname}}{{sugarvar key='name'}}{{/capture}}

<input type='text' name='{{$idname}}' id='{{$idname}}'
	size='{{$displayParams.size|default:30}}'
	{{if isset($displayParams.maxlength)}}maxlength='{{$displayParams.maxlength}}'{{elseif isset($vardef.len)}}maxlength='{{$vardef.len}}'{{/if}}
	value='{foreach from=$vals item=item name=tags}{$item}, {/foreach}'
	title='{{$vardef.help}}' tabindex='{{$tabindex}}' {{$displayParams.field}}>

{literal}
<script>
YUI().use("autocomplete", "autocomplete-filters", "autocomplete-highlighters", function (Y) {
  var inputNode = Y.one('#{/literal}{{$idname}}{literal}'),
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
 
    // After a tag is selected, send an empty query to update the list of tags.
    inputNode.ac.after('select', function () {
      inputNode.ac.sendRequest('');
      inputNode.ac.show();
    });
});
</script>
{/literal}
