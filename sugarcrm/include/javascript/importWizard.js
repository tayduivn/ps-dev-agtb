SUGAR.importWizard= {};

SUGAR.importWizard = function() {
	return {
	
		renderDialog: function(importModuleVAR,actionVar,sourceVar){
				
			SUGAR.importWizard.dialog = new YAHOO.widget.Dialog("importWizardDialog", 
			{ width : "880px",
			  height: "520px",
			  fixedcenter : true,
			  draggable:false,
			  visible : false, 
			  modal : true,
			  close:false
			 } );

			var oHead = document.getElementsByTagName('HEAD').item(0);
			var oBody = document.getElementsByTagName('BODY').item(0);
			if ( !document.getElementById( "sugar_grp_yui_widgets" )) {
					var oScript= document.createElement("script");
					oScript.type = "text/javascript";
					oScript.id = "sugar_grp_yui_widgets";
					oScript.src="include/javascript/sugar_grp_yui_widgets.js";
					oHead.appendChild( oScript);
			}
			
			if ( !document.getElementById( "sugar_grp_overlib" )) {
					var oScriptOverLib= document.createElement("script");
					oScriptOverLib.type = "text/javascript";
					oScriptOverLib.id = "sugar_grp_overlib";
					oScriptOverLib.src="include/javascript/sugar_grp_overlib.js";
					oHead.appendChild( oScriptOverLib);
					
					var overDiv= document.createElement("div");
					overDiv.id = "overDiv";
					overDiv.style.position = "absolute"
					overDiv.style.visibility = "hidden";
					overDiv.style.zIndex = "1000";
					overDiv.style.maxWidth = "400px";
					var parentEl = oBody.firstChild;
					parentEl.parentNode.insertBefore(overDiv, parentEl);

			}
			
			
			var success = function(data) {		
				eval(data.responseText);
				importWizardDialogDiv = document.getElementById('importWizardDialogDiv');
				submitDiv = document.getElementById('submitDiv');
				importWizardDialogDiv.innerHTML = response['html'];
				SUGAR.util.evalScript(response['html']);
				submitDiv.innerHTML = response['submitContent'];
				document.getElementById('importWizardDialog').style.display = '';												 
				SUGAR.importWizard.dialog.render();
				SUGAR.importWizard.dialog.show();

				eval(response['script']);


			}
			
			var cObj = YAHOO.util.Connect.asyncRequest('GET', 'index.php?module=Import&action='+actionVar+'&import_module='+importModuleVAR+'&source='+sourceVar, {success: success, failure: success});			
			return false;
			
			
			//document.getElementById('importWizardDialog_c').style.display = 'none';			
		},
		closeDialog: function() {
			
			SUGAR.importWizard.dialog.hide();
			
				importWizardDialogDiv = document.getElementById('importWizardDialogDiv');
				submitDiv = document.getElementById('submitDiv');
				importWizardDialogDiv.innerHTML = "";
				submitDiv.innerHTML = "";
		}	
			};
}();