/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
var InlineEditor = YUI({combine: true, timeout: 10000, base:"include/javascript/yui3/build/", comboBase:"index.php?entryPoint=getYUIComboFile&"}).use('anim', 'json','io-base', 'io-form', function(Y) {
	var editingId  = null;
	Y.fields = {};
	
	Y.editInPlace = function(id){
		this.editingId = id;
		var node = Y.one(this.editingId);
		var value = getEditor(node);
		displayEditInPlace(null,{responseText:value}, {
			id: node.getAttribute('id')
		});
		
	}
	
	getEditor = function(id){
		var node = Y.one( id);
		var id = node.getAttribute('id');
		var value = node.get('text').trim();
		switch(Y.fields[id]['type']){
			case 'fvarchar':
				return '<input type="text" name="' + id + '" value="' + value + '">';
			case 'fbool':
				return '<input type="checkbox" name="' + id + '" value="1">';
			default:
				var url = 'index.php?module=' + DCMenu.module + '&action=inlinefield&record=' + DCMenu.record +'&field=' + id;
		var id = Y.io(url, {
             method: 'GET',
             //XDR Listeners
 		    on: { 
 			    success: displayEditInPlace,
 			    failure: function(id, data) {
                     //Something failed..
                     alert('Feed failed to load..' + id + ' :: ' + data);
                 }
			
 		    },
			arguments: {
				id: id,
				restore:false
			}
         });
		 return 'Loading...';
		}
		
	}
	
	displayEditInPlace = function(id, data, arguments){
		 
		
		updateField(arguments.id, data.responseText, arguments.restore);
			var field = document.getElementById('InlineEditor').field.value;
			document.getElementById('InlineEditor')[field].focus();
		
		
		
	}
	
	updateField = function(id, value, inlineOnClick){
		var node = Y.one('#' +  id);
		if(!node)return;
		node.setContent(value);
		
		if (!inlineOnClick) {
			var parent = node.get('parentNode');
			Y.Event.purgeElement(parent, false, 'click');
		}else{
			Y.markAsEditable(id);
			
		}
	}
	
	
	Y.save = function(){
		var form = Y.one('#InlineEditor');
		var parent = form.get('parentNode');
		var field = document.getElementById('InlineEditor').field.value;
		var id = Y.io('index.php', {
             method: 'POST',
			 form: { 
	            id: form, 
	        }, 
             //XDR Listeners
 		    on: { 
 			    success: function(id, data, args){
					//console.log(args);
					var fields = Y.JSON.parse(data.responseText);
					for(i in fields){
						//console.log(i + "==" + args.id);
						if(i == args.id){
							
							updateField( i, fields[i], true);
						}else{
							updateField( i, fields[i], false);
						}
					}
					
					
				},
 			    failure: function(id, data) {
                     //Something failed..
                     alert('Feed failed to load..' + id + ' :: ' + data);
                 }
			
 		    },
			arguments: {
				id: field
			}
         });
					

		

	}
	
	Y.markAsEditable = function(id, field_id){
		var node = Y.one('#' + id);
		if(!node){
			return false;
		}
		if(node.onclick){
			return false;
		}
		var parent = node.get('parentNode');
		parent.addClass('editable');
		parent.on("inline|click",  function(event){
			InlineEditor.editInPlace('#' + id);
		});
		
	}
	
	Y.markListAsEditable = function(ids){
		for(i in ids){
			if (ids[i].editInPlace) {
				this.fields[i] = ids[i];
				this.markAsEditable(i);
			}
		}
	}
	

});
