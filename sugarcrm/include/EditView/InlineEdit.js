/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Sales Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/eula/sugarcrm-sales-subscription-agreement.html
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2010 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
var InlineEditor = YUI({combine: true, timeout: 10000, base:"include/javascript/yui3/build/", comboBase:"index.php?entryPoint=getYUIComboFile&"}).use('anim', 'json','io-base', 'io-form', function(Y) {
	var editingId  = null;
	Y.fields = {};
	
	Y.editInPlace = function(id){
		this.editingId = id;
		////console.log(this.editingId);
		var node = Y.one(this.editingId);
		var value = getEditor(node);
		////console.log(value);
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
			//console.log('did not find ' +  id);
			return false;
		}
		if(node.onclick){
			//console.log('what');
			return false;
		}
		//console.log('settingup');
		var parent = node.get('parentNode');
		parent.addClass('editable');
		parent.on("inline|click",  function(event){
			InlineEditor.editInPlace('#' + id);
		});
		
	}
	
	Y.markListAsEditable = function(ids){
		////console.log(ids);
		for(i in ids){
			if (ids[i].editInPlace) {
				this.fields[i] = ids[i];
				this.markAsEditable(i);
			}
		}
	}
	

});