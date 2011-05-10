
    
        //when called, this function will make ajax call to rebuild/repair js files
		var ajaxWizStatus = new SUGAR.ajaxStatusClass(); 
        function convertTemplate(id) {

            convert_mess = SUGAR.language.get('DCETemplates', 'LBL_CONVERSION_HAS_BEGUN');          
		    window.setTimeout("ajaxWizStatus.showStatus('"+convert_mess+"')",1000); 
  		    window.setTimeout('ajaxWizStatus.hideStatus()', 1500);         	
		    window.setTimeout("ajaxWizStatus.showStatus('"+convert_mess+"')",1700); 
		    window.setTimeout('ajaxWizStatus.hideStatus()', 3500);   		    
        	success = function() {  }//end success  		    
			postData = 'module=DCETemplates&action=convertTemplate&sugar_body_only=1&record='+id;
            YAHOO.util.Connect.asyncRequest('POST','index.php', {success: success, failure: success}, postData);
		}

