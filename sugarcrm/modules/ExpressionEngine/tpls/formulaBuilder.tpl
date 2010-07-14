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
<link rel="stylesheet" type="text/css" href="modules/ExpressionEngine/tpls/formulaBuilder.css" />
<table width="100%" id="formulaBuilder">
	<tr><td colspan=3><input name="formulaInput" id="formulaInput" size="120" value='{$formula}'/></td></tr>
	<tr>
		<td id="fieldsList" width="200" ><div id="fieldsGrid"></div></td>
		<td id="functionsList" width="200"><div id="functionsGrid"></div></td>
		<td id="functionDesc" style="text-align:center">Description</td>
	</tr>
</table>
<div style="width:100%;text-align:right"
<input type='button' class='button' name='cancelbtn' value='{sugar_translate module="ModuleBuilder" label="LBL_BTN_CANCEL"}'  
	onclick="ModuleBuilder.formulaEditorWindow.hide()" >
<input type='button' class='button' name='fsavebtn' value='{sugar_translate module="ModuleBuilder" label="LBL_BTN_SAVE"}' 
	onclick="if(SUGAR.expressions.saveCurrentExpression('{$target}'))ModuleBuilder.formulaEditorWindow.hide()">
</div>
<script src="modules/ExpressionEngine/javascript/formulaBuilder.js"></script>
<script type="text/javascript">
var fieldsArray = {$Field_Array};
var returnType = '{$returnType}';
SUGAR.expressions.initFormulaBuilder();
</script>