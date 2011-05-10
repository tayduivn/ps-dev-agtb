{*
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
*}
{ext_includes}
{literal}
<script type="text/javascript">
	displayResult = function(result){
		Ext.getCmp('formulaBuilderWindow').close();
		console.log(result);
	};
	positionWindow = function() {
		//Hack for window.center() which is broken under FF3
		var win = Ext.getCmp('formulaBuilderWindow');
		var view = {width:document.body.clientWidth, height:document.body.clientHeight};//Ext.getBody().getSize();
		win.setPosition(Math.max(0, (view.width - win.getSize().width) / 2), 
						Math.max(0, (view.height -win.getSize().height) / 2));
	}
	showEditor = function() {
	var EditorWindow = new Ext.Window({
		id: 'formulaBuilderWindow',
		autoLoad: {
			url:"index.php",
			params: {
				module:"ExpressionEngine",
				action:"editFormula",
				onSave:"displayResult",
				onLoad:"positionWindow",
				onClose:"function(){Ext.getCmp('formulaBuilderWindow').close();}",
				loadExt:false,
				embed: true,
				targetModule:"Opportunities"
			},
			scripts: true
		},
		renderTo:"editorDiv",
		modal:true,
		plain:true,
		resizable:false,
		nodyBorder:false,
		width:800
		//autoHeight:true,
		//autoWidth:true
	});
	EditorWindow.show();
}
</script>
{/literal}
<input class="button" type="button" onclick="showEditor()" value="Show"/>
<div id="editorDiv"></div>