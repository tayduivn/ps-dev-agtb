if(typeof(SUGAR.AutoComplete) == 'undefined') SUGAR.AutoComplete = {};

SUGAR.AutoComplete.getSourceFromOptions = function(options_index){
    var return_arr = new Array();
    var opts = SUGAR.language.get('app_list_strings', options_index);
    if(typeof(opts) != 'undefined'){
        for(key in opts){
			// Since we are using auto complete, we excluse blank dropdown entries since they can just leave it blank
			if(key != '' && opts[key] != ''){
	            var item = [];
	            item['key'] = key;
	            item['text'] = opts[key];
	            return_arr.push(item);
			}
        }
    }
    return return_arr;
}
