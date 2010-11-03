{*

/**
 * The contents of this file are subject to the SugarCRM Enterprise Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
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

 */

*}
{include file="modules/P1_Partners/tpls/od_instance_key_validation.tpl"}
<script type='text/javascript' src='{sugar_getjspath file='include/javascript/popup_helper.js'}'></script>
<script src='custom/include/javascript/custom_javascript.js'></script>
<script>

/**
* Create an array which determines if we should highlight the row to indicate an alert.  This array is then read by
* the DataSource cutom formatter.
*/
var a_showAlert = Array();

{foreach name=rowIteration from=$data key=id item=rowData}

	{if ($rowData.SIXTYMIN_OPP_C != "0") }
	
		a_showAlert['{$rowData.ID}'] = true;
		a_showAlert['{$rowData.ID}-c'] = "#B6B6FF";
	
		
	{else}
	
		{if ($rowData.ACCEPTED_BY_PARTNER_C == "Rejected" || $rowData.TASKS > 0) }
		a_showAlert['{$rowData.ID}'] = true;
		a_showAlert['{$rowData.ID}-c'] = "#FFB6B6";

		{else}


		    {if ($rowData.CONFLICT_C != "0") }
		        a_showAlert['{$rowData.ID}'] = true;
		        a_showAlert['{$rowData.ID}-c'] = "#FFA240";
		    {else}
		        a_showAlert['{$rowData.ID}'] = false;
		        a_showAlert['{$rowData.ID}-c'] = "";
            {/if}

		{/if}
		

	{/if}
	
	

{/foreach}
</script>

{if $overlib}
	<script type='text/javascript' src='{sugar_getjspath file='include/javascript/sugar_grp_overlib.js'}'></script>
	<div id='overDiv' style='position:absolute; visibility:hidden; z-index:1000;'></div>
{/if}

{if $prerow}
	{$multiSelectData}
{/if}
{assign var='jsShowPanel' value = ""}
<table cellpadding='0' cellspacing='0' width='100%' border='0' class='list view' style="margin-top: 10px;">
{include file='modules/P1_Partners/tpls/ListViewPagination.tpl'}
<tr height='20'>
	<td colspan='100'><div id="markup" class="yui-skin-sam"> <table  cellpadding='0' cellspacing='0' width='100%' border='0' id='listviewdata' class='list view'>
		
	{counter start=$pageData.offsets.current print=false assign="offset" name="offset"}	
	{foreach name=rowIteration from=$data key=id item=rowData}
	    {counter name="offset" print=false}

		{if $smarty.foreach.rowIteration.iteration is odd}
			{assign var='_rowColor' value=$rowColor[0]}
		{else}
			{assign var='_rowColor' value=$rowColor[1]}
		{/if}
		<tr height='20' class='{$_rowColor}S1' >
			<td style='display:none' id='row_id'>{$rowData.ID}</td>
			{if $prerow}
			<td width='1%' class='nowrap'>
			 {if !$is_admin && is_admin_for_user && $rowData.IS_ADMIN==1}
					<input type='checkbox' disabled="disabled" class='checkbox' value='{$rowData.ID}'>
					{$pageData.additionalDetails.$id}
			 {else}
                    <input onclick='sListView.check_item(this, document.MassUpdate); checkBoxStatus();' type='checkbox' class='checkbox' name='mass[]' value='{$rowData.ID}'>
                    {$pageData.additionalDetails.$id}			 
			 {/if}
			</td>
			{/if}
			{counter start=0 name="colCounter" print=false assign="colCounter"}

			{foreach from=$displayColumns key=col item=params}
				<td scope='row' align='{$params.align|default:'left'}' valign="top" {if ($params.type == 'teamset')}class="nowrap"{/if}>{strip}
					{if $params.link && !$params.customCode}
						
						<{$pageData.tag.$id[$params.ACLTag]|default:$pageData.tag.$id.MAIN} href="#" onMouseOver="javascript:lvg_nav('{if $params.dynamic_module}{$rowData[$params.dynamic_module]}{else}{$params.module|default:$pageData.bean.moduleDir}{/if}', '{$rowData[$params.id]|default:$rowData.ID}', 'd', {$offset}, this)"  onFocus="javascript:lvg_nav('{if $params.dynamic_module}{$rowData[$params.dynamic_module]}{else}{$params.module|default:$pageData.bean.moduleDir}{/if}', '{$rowData[$params.id]|default:$rowData.ID}', 'd', {$offset}, this)">{$rowData.$col}</{$pageData.tag.$id[$params.ACLTag]|default:$pageData.tag.$id.MAIN}>
						
					{elseif $params.customCode} 
						{sugar_evalcolumn_old var=$params.customCode rowData=$rowData}
					{elseif $params.currency_format} 
						{sugar_currency_format 
                            var=$rowData.$col 
                            round=$params.currency_format.round 
                            decimals=$params.currency_format.decimals 
                            symbol=$params.currency_format.symbol
                            convert=$params.currency_format.convert
                            currency_symbol=$params.currency_format.currency_symbol
						}
					{elseif $params.type == 'bool'}
							<input type='checkbox' disabled=disabled class='checkbox'
							{if !empty($rowData[$col])}
								checked=checked
							{/if}
							/>

					{elseif $params.type == 'teamset'}
                    {sugar_teamset_list row=$rowData col=$col vardef=$params}

					{elseif $params.type == 'multienum'}
						{if !empty($rowData.$col)} 
							{counter name="oCount" assign="oCount" start=0}
							{assign var="vals" value='^,^'|explode:$rowData.$col}
							{foreach from=$vals item=item}
								{counter name="oCount"}
								{sugar_translate label=$params.options select=$item}{if $oCount !=  count($vals)},{/if} 
							{/foreach}	
						{/if}
					{else}	
						{$rowData.$col|default:"&nbsp;"}
					{/if}
					
				{/strip}</td>
				{counter name="colCounter"}
			{/foreach}
			{if !empty($quickViewLinks)}
			<td width='7%' nowrap>
				{if $pageData.access.edit}
					<a title='{$editLinkString}' href="javascript: void(0);"   onclick="getFormContents('{$rowData.ID}');YAHOO.example.container.panel1.show();">
					<img border=0 src='{sugar_getimagepath file='edit_inline.gif'}'>
					</a>
				{/if}
			</td>
			
	    	</tr>
			{/if}
	 	
	    
	{/foreach}
	</td></tr></table></div>
{include file='include/ListView/ListViewPagination.tpl'}
</table>
{if $contextMenus}
<script>
	{$contextMenuScript}
{literal}function lvg_nav(m,id,act,offset,t){if(t.href.search(/#/) < 0){return;}else{if(act=='pte'){act='ProjectTemplatesEditView';}else if(act=='d'){ act='DetailView';}else{ act='EditView';}{/literal}url = 'index.php?module='+m+'&offset=' + offset + '&stamp={$pageData.stamp}&return_module='+m+'&action='+act+'&record='+id;t.href=url;{literal}}}{/literal}
{literal}function lvg_dtails(id){{/literal}return SUGAR.util.getAdditionalDetails( '{$params.module|default:$pageData.bean.moduleDir}',id, 'adspan_'+id);{literal}}{/literal}
</script>
{/if}

<link rel="stylesheet" type="text/css" href='{sugar_getjspath file='include/javascript/yui/build/fonts/fonts-min.css'}' />
<link rel="stylesheet" type="text/css" href='{sugar_getjspath file='include/javascript/yui/build/datatable/assets/skins/sam/datatable.css'}' />
<link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/2.7.0/build/datatable/assets/skins/sam/datatable.css" />
<link rel="stylesheet" type="text/css" href="{sugar_getjspath file='include/javascript/yui/build/container/assets/skins/sam/container.css}" />



<script type='text/javascript' src='{sugar_getjspath file='include/javascript/yui/build/yahoo-dom-event/yahoo-dom-event.js'}'></script>
<script type='text/javascript' src='{sugar_getjspath file='include/javascript/yui/build/element/element-min.js'}'></script>
<script type='text/javascript' src='{sugar_getjspath file='include/javascript/yui/build/yahoo/yahoo-min.js}'></script>
<script type='text/javascript' src='{sugar_getjspath file='include/javascript/yui/build/datasource/datasource-min.js'}'></script>
<script type='text/javascript' src='{sugar_getjspath file='include/javascript/yui/build/datatable/datatable-min.js'}'></script>
<script type='text/javascript' src='{sugar_getjspath file='include/javascript/yui/build/dom/dom-min.js'}'></script>
<script type='text/javascript' src='{sugar_getjspath file='include/javascript/yui/build/calendar/calendar-min.js'}'></script>
<script type="text/javascript" src="{sugar_getjspath file='include/javascript/yui/build/connection/connection-min.js"></script>

<script type="text/javascript" src="{sugar_getjspath file='include/javascript/yui/build/dragdrop/dragdrop-min.js}"></script>
<script type="text/javascript" src="{sugar_getjspath file='include/javascript/yui/build/container/container-min.js}"></script>


<script type="text/javascript" src="http://yui.yahooapis.com/2.7.0/build/paginator/paginator-min.js"></script>


{literal}

<style>
.fullwidth table{
    width:100%;
    
}
.fullwidth td{
    padding:0px; !important
    
}
.yui-dt-mask {
    position:absolute;
    z-index:50;
}
</style>

<script>
	function checkBoxStatus() {
		if(sugarListView.get_checks_count() > 0) {
		document.getElementById('assign_button').disabled = false;
		} else {
		document.getElementById('assign_button').disabled = true;
		}
	}
	function toggleMore(spanId, img_id, module, action, params){
		go = function() {
			oReturn = function(body, caption, width, theme) {
						return overlib(body, CAPTION, caption, STICKY, MOUSEOFF, 1000, WIDTH, width, CLOSETEXT, ('<img border=0 style="margin-left:2px; margin-right: 2px;" src=themes/' + theme + '/images/close.gif>'), CLOSETITLE, 'Click to Close', CLOSECLICK, FGCLASS, 'olFgClass', CGCLASS, 'olCgClass', BGCLASS, 'olBgClass', TEXTFONTCLASS, 'olFontClass', CAPTIONFONTCLASS, 'olCapFontClass', CLOSEFONTCLASS, 'olCloseFontClass', REF, spanId, REFC, 'LL', REFX, 13);
					}
			success = function(data) {
						eval(data.responseText);
	
						SUGAR.util.additionalDetailsCache[spanId] = new Array();
						SUGAR.util.additionalDetailsCache[spanId]['body'] = result['body'];
						SUGAR.util.additionalDetailsCache[spanId]['caption'] = result['caption'];
						SUGAR.util.additionalDetailsCache[spanId]['width'] = result['width'];
						SUGAR.util.additionalDetailsCache[spanId]['theme'] = result['theme'];
						ajaxStatus.hideStatus();
						return oReturn(SUGAR.util.additionalDetailsCache[spanId]['body'], SUGAR.util.additionalDetailsCache[spanId]['caption'], SUGAR.util.additionalDetailsCache[spanId]['width'], SUGAR.util.additionalDetailsCache[spanId]['theme']);
					}
	
					if(typeof SUGAR.util.additionalDetailsCache[spanId] != 'undefined')
						return oReturn(SUGAR.util.additionalDetailsCache[spanId]['body'], SUGAR.util.additionalDetailsCache[spanId]['caption'], SUGAR.util.additionalDetailsCache[spanId]['width'], SUGAR.util.additionalDetailsCache[spanId]['theme']);
	
					if(typeof SUGAR.util.additionalDetailsCalls[spanId] != 'undefined') // call already in progress
						return;
					ajaxStatus.showStatus(SUGAR.language.get('app_strings', 'LBL_LOADING'));
					url = 'index.php?module='+module+'&action='+action+'&'+params;
					SUGAR.util.additionalDetailsCalls[spanId] = YAHOO.util.Connect.asyncRequest('GET', url, {success: success, failure: success});
	
					return false;
		}
		SUGAR.util.additionalDetailsRpcCall = window.setTimeout('go()', 250);
	}
	
	/**  Begin Inline Editing Code **/
	
	
	YAHOO.sugar = {};
	YAHOO.sugar.dds = {};
	
	/*
	* Create DD objects with key/value association that can be used with YUI's functions.
	*/
	YAHOO.sugar.SugarToYahooDD = function(field, dropdown_name)
	{
		if(dropdown_name == 'sales_stage_dom')
		{
			dropdown_name = 'sales_stage_restricted_dom';
		}
		data = SUGAR.language.get('app_list_strings', dropdown_name);
		r = [];
		for(i in data){
		
			if(dropdown_name == 'opportunity_type_dom' && i == '')
				continue;
			//instead of passing label and index, we pass label as both label and index to overcome YUI bug where the labels do not match values gives user a random label onclick
			r[r.length] = {label:data[i], value: data[i] };
		}
		YAHOO.sugar.dds[field] = data;
		return r;
	}

	/*
	*  Function to save drop down changes for ajax call.
	*/
	YAHOO.sugar.saveDropDownChange = function(callback, new_value){
	
		//we need to get the value of the selected option using the label of the option, YUI has a bug where the labels do not match values
		dropdown_name = this.dropdown_field;
		if(dropdown_name == 'sales_stage_dom')
		{
			dropdown_name = 'sales_stage_restricted_dom';
		}
		data = SUGAR.language.get('app_list_strings', dropdown_name);
		data_r = [];
		for(i in data){
			if(dropdown_name == 'opportunity_type_dom' && i == '')
				continue;
			data_r[data[i]] = i; 
		}
		
		new_value = data_r[new_value];
		
		
		var r = this.getRecord();
		var field = this.getColumn().field.toLowerCase();
		var query = 'module=Opportunities&action=ajaxformsave&record=' + r._oData.id + '&' +  field + '=' + escape(new_value);
		//asandberg: asyncSubmitter will block UI Thread so call it now before making async request.  Otherwise the cell editor will not be closed until after server save.
		callback(true,YAHOO.sugar.dds[field][new_value]);
		YAHOO.util.Connect.asyncRequest('POST', 'index.php', {success:YAHOO.sugar.savedGenericChange, failure: YAHOO.sugar.failedChange, argument:{callback:callback,field:field, value:new_value}}, query);
	}
	/*
	*  Function to save currency changes for ajax call.  Currency fields require a saved_field_name definition in the list view file as they require
	*  two fields- one for displaying and one for saving.  Eg (amount vs amount_usdollar).  
	*/
	YAHOO.sugar.saveCurrencyChange = function(callback, new_value){

		if( this.save_field_name != 'undefined')
			var field = this.save_field_name;
		else
			var field = this.getColumn().field.toLowerCase();
			
		var r = this.getRecord();

		//Get rid of any currency symbols present.
		var new_value_formatted = new_value.replace(currency_symbol, "");
		var query = 'module=Opportunities&action=ajaxformsave&record=' + r._oData.id + '&' +  field + '=' + escape(new_value_formatted);
		//asandberg: asyncSubmitter will block UI Thread so call it now before making async request.  Otherwise the cell editor will not be closed until after server save.
		callback(true,new_value);
		YAHOO.util.Connect.asyncRequest('POST', 'index.php', {success:YAHOO.sugar.savedCurrencyChange, failure: YAHOO.sugar.failedChange, argument:{callback:callback,field:field, value:new_value, cell:this}}, query);
	}
	
	/**
	*  Save generic changes for ajax callback.  Currently used for date and int type fields.
	*/
	YAHOO.sugar.saveGenericChange = function(callback, new_value){
		var r = this.getRecord();
		var field = this.getColumn().field.toLowerCase();
		var query = 'module=Opportunities&action=ajaxformsave&record=' + r._oData.id + '&' +  field + '=' + escape(new_value);
		//asandberg: asyncSubmitter will block UI Thread so call it now before making async request.  Otherwise the cell editor will not be closed until after server save.
		ajaxStatus.showStatus(SUGAR.language.get('app_strings', 'LBL_SAVED'));
		callback(true,new_value);
		YAHOO.util.Connect.asyncRequest('POST', 'index.php', {success:YAHOO.sugar.savedGenericChange, failure: YAHOO.sugar.failedChange, argument:{callback:callback,field:field, value:new_value}}, query);

	}

	/**
	*  Generic callback for when an ajax save is successful.  Will just display a nice unobtrusive  message.
	*/
	YAHOO.sugar.savedGenericChange = function(o){
		
		ajaxStatus.hideStatus();
	}

	/**
	*  For currency fields that are changed, make a call back to the server to get the formatted value
	*  that was just saved.  This prevents us from needing to figure out which currency id was used during the save.
	*/
	YAHOO.sugar.savedCurrencyChange = function(o){

		var r = o.argument.cell.getRecord();
		var field = o.argument.cell.getColumn().field.toLowerCase();
		var query = 'entryPoint=getUpdatedCurrencyFieldValue&record=' + r._oData.id + '&field=' +  field;
		var callback_save = function(os) {
			var v = YAHOO.lang.JSON.parse(os.responseText);
			os.argument.cell.getDataTable().updateCell(r, os.argument.cell.getColumn(), v);
		};
		
		YAHOO.util.Connect.asyncRequest('POST', 'index.php', {success:callback_save, failure: function(o){}, argument:{cell:o.argument.cell}}, query);
	}
	
	
	/**
	*  Generic callback for when an ajax save has failed.  Will just display a nice unobtrusive error message.
	*/
	YAHOO.sugar.failedChange = function(o){
		callback = o.argument.callback;
		callback();
	}
	/**
	* Custom formatter that will be used to validate currency fields.  Our currency fields on the listview displays a currency symbol so
	* we account for that when validating input.  Users have the option of specifying the symbol.  We then use Sugars own isFloat function
	* for the validation.  If the input is valid we bubble up undefined which causes the cell to be reset.
	*
	* @method DataTable.formatNumber
	* @param oData {Object} Data value for the cell, or null.
	*/
	YAHOO.widget.DataTable.validateCurrency = function(oData)
	{
		//Remove the currency symbol if present before the validation
		var noSymbolData= oData.replace(currency_symbol, "");
		//Use the default sugar isFloat check.

		if( isFloat(noSymbolData) )
			return oData;
		else
			return undefined;
	}
   
	/**
	*  Custom object that will be used for date columns to present Sugars Calendar object.
	*  We will inherit from the generic CellEditor class.
	*/
	YAHOO.widget.SugarDateCellEditor = function (oConfigs) 
	{
		this._sId = "yui-sugardateeditor" + YAHOO.widget.BaseCellEditor._nCount++;
		YAHOO.widget.SugarDateCellEditor.superclass.constructor.call(this, 'sugardate', oConfigs);
	};

	YAHOO.lang.extend(YAHOO.widget.SugarDateCellEditor, YAHOO.widget.BaseCellEditor, 
	{
		/**
		*  Show function which creates a custom calendar object using Sugars DHTML cal.  YUI does include a date picker but using Sugars to
		*  stay consistent with the UI.
		*/
		show: function ()
		{
			//Get the current td object so we know where to display calendar.
			var containerEl = this.getTdEl();
			var cal=new Calendar(0, '', null,null,null) ;
			//Pass in ourselves so we have a link for when a user selects a date.
			cal.cellEditor = this;
			cal.onClose = function (){
				this.hide(); 
			},
			cal.onSelected=function(d, new_selected_date){ 
				//Make sure the user didn't click the nav icons.
				if(this.dateClicked)
				{
					this.cellEditor.value = new_selected_date;  
					this.cellEditor.save();  
					this.hide(); 
				}
			}
			//The following object needs to be set.  The dhtml calendar expects a valid input field of type text so this is a workaround for their buggy code.
			cal.inputField = true;
			//Generic setup options
			cal.dateFormat = cal_date_format;
			cal.showsTime=false;
			cal.setDateStatusHandler(null);
			cal.isPopup = true;
			cal.create();
			cal.showAtElement(containerEl,"Br");
		},
		/*
		* Need to override superclass function to return correct value
		*/
		getInputValue : function()
		{
			return this.value;
		}
	});

	//Static values need to be copied in although at this point we aren't really using any.
	YAHOO.lang.augmentObject(YAHOO.widget.SugarDateCellEditor, YAHOO.widget.BaseCellEditor);

	//Create the datatable onload.
	YAHOO.util.Event.addListener(window, "load", function() {
		YAHOO.sugar.EnhanceFromMarkup = new function() {
			this.myColumnDefs = [

			{key:'checkbox', label:"<input type='checkbox' class='checkbox' name='massall' value='' onclick='sListView.check_all(document.MassUpdate, \"mass[]\", this.checked); checkBoxStatus();' />"}
			{/literal}
			{foreach from=$displayColumns key=colHeader item=params} 
			,{ldelim} key:"{$colHeader}", resizable:false, label:"{strip}
			
                         		 {if $params.sortable|default:true}
                 			 	{if $params.url_sort}
                        				<a href='{$pageData.urls.orderBy}{$params.orderBy|default:$colHeader|lower}' class='listViewThLinkS1'>
                 				{else}
                       				 {if $params.orderBy|default:$colHeader|lower == $pageData.ordering.orderBy}
                           					 <a href='javascript:sListView.order_checks(\"{$pageData.ordering.sortOrder|default:ASCerror}\", \"{$params.orderBy|default:$colHeader|lower}\" , \"{$pageData.bean.moduleDir}2_{$pageData.bean.objectName|upper}_ORDER_BY\")' class='listViewThLinkS1'>
                       				 {else}
                            					<a href='javascript:sListView.order_checks(\"ASC\", \"{$params.orderBy|default:$colHeader|lower}\" , \"{$pageData.bean.moduleDir}2_{$pageData.bean.objectName|upper}_ORDER_BY\")' class='listViewThLinkS1'>
                       				 {/if}
                   			{/if}
                    			{sugar_translate label=$params.label module=$pageData.bean.moduleDir}</a>&nbsp;&nbsp;
					{if $params.orderBy|default:$colHeader|lower == $pageData.ordering.orderBy}
						{if $pageData.ordering.sortOrder == 'ASC'}
							{capture assign='imageName'}arrow_down.{$arrowExt}{/capture}
							<img border='0' src='{sugar_getimagepath file=$imageName}' width='{$arrowWidth}' height='{$arrowHeight}' align='absmiddle' alt='{$arrowAlt}'>
						{else}
							{capture assign='imageName'}arrow_up.{$arrowExt}{/capture}
							<img border='0' src='{sugar_getimagepath file=$imageName}' width='{$arrowWidth}' height='{$arrowHeight}' align='absmiddle' alt='{$arrowAlt}'>
						{/if}
					{else}
						{capture assign='imageName'}arrow.{$arrowExt}{/capture}
						<img border='0' src='{sugar_getimagepath file=$imageName}' width='{$arrowWidth}' height='{$arrowHeight}' align='absmiddle' alt='{$arrowAlt}'>
					{/if}
				{else}
					{sugar_translate label=$params.label module=$pageData.bean.moduleDir}
				{/if}
			{/strip}"

			
			{if $params.inline_editable == true}
				{if $params.type == 'enum'}
					,editor: new YAHOO.widget.DropdownCellEditor({ldelim}asyncSubmitter:YAHOO.sugar.saveDropDownChange, dropdown_field:'{$params.options}', dropdownOptions:YAHOO.sugar.SugarToYahooDD('{$colHeader}'.toLowerCase(), '{$params.options}'), disableBtns:true{rdelim})
				{elseif $params.type == 'currency'}
					,editor: new YAHOO.widget.TextboxCellEditor({ldelim}asyncSubmitter:YAHOO.sugar.saveCurrencyChange, save_field_name:'{$params.save_as_field_name}' ,validator:YAHOO.widget.DataTable.validateCurrency, disableBtns:true {rdelim})
				{elseif $params.type == 'int'}
					,editor: new YAHOO.widget.TextboxCellEditor({ldelim}asyncSubmitter:YAHOO.sugar.saveGenericChange, validator:YAHOO.widget.DataTable.validateNumber, disableBtns:true {rdelim})
				{elseif $params.type == 'date'}
					,editor: new YAHOO.widget.SugarDateCellEditor({ldelim}asyncSubmitter:YAHOO.sugar.saveGenericChange{rdelim} )
				{/if}
			{/if}
			{rdelim}
			{/foreach}

			, {ldelim}key:'editbox_placeholder', label:"" {rdelim}

			{literal}
			];
			this.myDataSource = new YAHOO.util.DataSource(YAHOO.util.Dom.get("listviewdata"));
			this.myDataSource.responseType = YAHOO.util.DataSource.TYPE_HTMLTABLE;
			this.myDataSource.responseSchema = {
				fields: [{key:'id'},{key:'checkbox'}
				{/literal}
					{foreach from=$displayColumns key=colHeader item=params}
						{if $params.type == 'date'}
							,{ldelim} key:"{$colHeader}"{rdelim}
						{elseif $params.type == 'currency'}
							,{ldelim} key:"{$colHeader}" {rdelim}
						{*//EDDY CUSTOMIZATION IT Tix 12686
						//we do not want to stop int fields from being edited, but the score
						//field is an int that should not be edited, so look specifically
						// for score column*}
						{elseif $colHeader == 'SCORE_C'}
							,{ldelim} key:"{$colHeader}" {rdelim}
						{elseif $params.type == 'int' }
							,{ldelim} key:"{$colHeader}", parser:"number" {rdelim}
						{else}
							,{ldelim} key:"{$colHeader}"{rdelim}
						{/if}
					{/foreach}
					,{ldelim} key:"editbox_placeholder"{rdelim}
				{literal}
				]
			};
			
			/**
			* Function to format row so that records with alerts will be highlighted.
			*/
			var listViewAlertFormatter = function(elTr, oRecord) 
			{
				var id = oRecord.getData('id');
				var colorid = id+"-c";
				if(a_showAlert[id])
				YAHOO.util.Dom.setStyle(elTr, 'background-color', a_showAlert[colorid]);
				return true;
			};

			var tableConfigs = {className:'fullwidth',formatRow: listViewAlertFormatter};

			this.myDataTable = new YAHOO.widget.DataTable("markup", this.myColumnDefs, this.myDataSource,tableConfigs);
			
			/**
			*   Setup the necessary events for cell highlighting.
			*/
			var highlightEditableCell = function(oArgs) 
			{
				var elCell = oArgs.target;
				if(YAHOO.util.Dom.hasClass(elCell, "yui-dt-editable")) 
				{
					this.highlightCell(elCell);
				}
			};
			this.myDataTable.subscribe("cellMouseoverEvent", highlightEditableCell);
			this.myDataTable.subscribe("cellMouseoutEvent", this.myDataTable.onEventUnhighlightCell);
			this.myDataTable.subscribe("cellClickEvent", this.myDataTable.onEventShowCellEditor);
		};
	});

{/literal}

//The following are needed for localization.
var num_grp_sep = '{$NUM_GRP_SEP}';
var dec_sep = '{$DEC_SEP}';
var currency_symbol = '$';
</script>
{$tinyPath}
<script>
{literal}


var handleSuccess = function(o){
	var formContents = document.getElementById('formContents');
	formContents.innerHTML = "";
	if(o.responseText !== undefined){
		
		response = o.responseText;
		if(!detectMIE()) {
		newForm = document.createElement("form");
		newForm.setAttribute("action","index.php?entryPoint=quickEditSave");
		newForm.setAttribute("method","POST");
		newForm.setAttribute("name","quickEditForm");
		newForm.setAttribute("id","quickEditForm");
		formContents.appendChild(newForm);
		newDiv = document.createElement("div");
		newDiv.setAttribute("id","quickEditDiv");
		newDiv.innerHTML = response;
		newForm.appendChild(newDiv);
		} else {
		newDiv = document.createElement("div");
		newDiv.setAttribute("id","quickEditDiv");
		newDiv.innerHTML = response;
		formContents.appendChild(newDiv);
		}

Calendar.setup ({
	inputField : "Opportunitiesdate_closed",
	ifFormat : "{/literal}{$CAL_DATEFORMAT}{literal}",
    showsTime : false,
    button : "Opportunitiesdate_closed_trigger",
    singleClick : true,
    step : 1
});

Calendar.setup ({
	inputField : "Opportunitiesnext_step_due_date", 
	ifFormat : "{/literal}{$CAL_DATEFORMAT}{literal}", 
	showsTime : false, 
	button : "Opportunitiesnext_step_due_date_trigger", 
	singleClick : true, 
	step : 1
});


	}
};

var handleFailure = function(o){

	if(o.responseText !== undefined){
		document.getElementById('formContents').innerHTML = "Unable to contact server";
	}
};


var callback =
{
  success:handleSuccess,
  failure:handleFailure,
  argument:['foo','bar']
};

function getFormContents(recordId) {
	postData = "index.php?module=P1_Partners&action=quickedit&record=";		
	sUrl = postData+recordId;
	var request = YAHOO.util.Connect.asyncRequest('GET', sUrl, callback);
	var container = document.getElementById('container');
	container.style.display = "block";
}
/******************* START EVAL CREATE WIZARD CODE *****************/
var handleSuccessEvalWiz = function(o){
	var formContentsEvalWiz = document.getElementById('formContentsEvalWiz');
	formContentsEvalWiz.innerHTML = "";
	if(o.responseText !== undefined){
		response = o.responseText;
		if(!detectMIE()) {
		newForm = document.createElement("form");
		newForm.setAttribute("action","index.php?entryPoint=evalWizardSave");
		newForm.setAttribute("method","POST");
		newForm.setAttribute("name","evalWizForm");
		newForm.setAttribute("id","evalWizForm");
		formContentsEvalWiz.appendChild(newForm);
		newDiv = document.createElement("div");
		newDiv.setAttribute("id","evalWizDiv");
		newDiv.innerHTML = response;
		newForm.appendChild(newDiv);
		} else {
		newDiv = document.createElement("div");
		newDiv.setAttribute("id","evalWizDiv");
		newDiv.innerHTML = response;
		formContentsEvalWiz.appendChild(newDiv);
		}

Calendar.setup ({
	inputField : "eval_end_date",
	ifFormat : "{/literal}{$CAL_DATEFORMAT}{literal}",
    showsTime : false,
    button : "EvalEndDate_trigger",
    singleClick : true,
    step : 1
});
	}
};

var handleFailureEvalWiz = function(o){
	if(o.responseText !== undefined){
		document.getElementById('formContentsEvalWiz').innerHTML = "Unable to contact server";
	}
};


var callbackEvalWiz =
{
  success:handleSuccessEvalWiz,
  failure:handleFailureEvalWiz,
  argument:['foo','bar']
};

function getformContentsEvalWiz(recordId) {
	postData = "index.php?module=P1_Partners&action=evalwizard&record=";		
	sUrl = postData+recordId;
	var request = YAHOO.util.Connect.asyncRequest('GET', sUrl, callbackEvalWiz);
	var container = document.getElementById('container');
	container.style.display = "block";
}

function showContactsEW() {
	var account_id = document.getElementById('opp_account_id').value;
	var callbackew = {
        	success: function(o) {
               	document.getElementById('contactsew').innerHTML = o.responseText;
               	}
   	} 
       	var connectionObject = YAHOO.util.Connect.asyncRequest ('GET', 'index.php?module=P1_Partners&action=getcontacts&eval_flag=true&account_id='+account_id, callbackew);
}

function submitEvalReq() {
	if(document.getElementById('OpportunitiesSalesStage').value == "Interested_Prospect" || document.getElementById('OpportunitiesSalesStage').value == "Initial_Opportunity" ) {
                alert('This Opp has not reached the sufficent sales stage to create a 30 day evaluation. Sales stage must be at least Discovery(25%).');
                return false;
    }
	if(document.getElementById('invalidinstance').value == "1") {
                alert('OnDemand domain name is invalid. Please chose another.');
                document.getElementById('instance_name').focus();
                return false;
    }
	if(document.getElementById('eval_end_date').value == "") {
                alert('Missing required field: Evaluation End Date. Please update.');
                document.getElementById('eval_end_date').focus();
                return false;
    }
 	if(!document.getElementById('email_flag') && !document.getElementById('P1_Partnerscontact_id')) {
                alert('Please select an account contact for the email notification to be sent to.');
                return false;
    }
	if(!detectMIE()) {
	var formObject = document.getElementById('evalWizForm');
	formObject.submit();
	} else {
		submitEvalWizard();
	}
}

function submitEvalUpdateReq() {
	if(document.getElementById('eval_end_date').value == "") {
                alert('Missing required field: Evaluation End Date. Please update.');
                document.getElementById('eval_end_date').focus();
                return false;
    }
	if(!detectMIE()) {
	var formObject = document.getElementById('evalWizForm');
	formObject.submit();
	} else {
		submitEvalUpdateWizard();
	}
}

function submitEvalUpdateWizard(){
	var eval_end_date = document.getElementById('eval_end_date').value;
	var opp_account_id = document.getElementById('opp_account_id').value;
	var Opportunitiesid = document.getElementById('Opportunitiesid').value;
	var P1_PartnersEvalWizardSave = document.getElementById('P1_PartnersEvalWizardSave').value;
	var return_module = document.getElementById('return_module').value;
	var return_action = document.getElementById('return_action').value;
	
	var postData = "entryPoint=evalWizardSave";
	postData += "&eval_end_date="+eval_end_date;
	postData += "&opp_account_id="+opp_account_id ;
	postData += "&Opportunitiesid="+Opportunitiesid;
	postData += "&P1_PartnersEvalWizardSave="+P1_PartnersEvalWizardSave;
	postData += "&P1_PartnersEvalWizardSave=true";
	postData += "&eval_update=true";
	postData += "&IE=TRUE";
	var request = YAHOO.util.Connect.asyncRequest('POST', sUrl, callbackEvalWiz, postData);
}

function submitEvalWizard(){
    // begin fix - jwhitcraft - ITR: 14024 - Make sure you get the checked value instead of just the first value
	if (YAHOO.util.Selector.query('input[name^=P1_Partnerscontact]:checked')[0] != null) {
		var P1_Partnerscontact_id = YAHOO.util.Selector.query('input[name^=P1_Partnerscontact]:checked')[0].value;
    }
	// end fix
	var eval_end_date = document.getElementById('eval_end_date').value;
	var flavor = YAHOO.util.Selector.query('input[name^=flavor]:checked')[0].value;
	var instance_name = document.getElementById('instance_name').value;
	var data_center = YAHOO.util.Selector.query('input[name^=data_center]:checked')[0].value;
	var opp_account_id = document.getElementById('opp_account_id').value;
	var Opportunitiesid = document.getElementById('Opportunitiesid').value;
	var P1_PartnersEvalWizardSave = document.getElementById('P1_PartnersEvalWizardSave').value;
	var return_module = document.getElementById('return_module').value;
	var return_action = document.getElementById('return_action').value;
	
	var postData = "entryPoint=evalWizardSave";
	postData += "&eval_end_date="+eval_end_date;
	postData += "&flavor="+flavor;
	postData += "&instance_name="+instance_name;
	postData += "&data_center="+data_center;
	postData += "&opp_account_id="+opp_account_id ;
	postData += "&Opportunitiesid="+Opportunitiesid;
	postData += "&P1_PartnersEvalWizardSave="+P1_PartnersEvalWizardSave;
	postData += "&P1_PartnersEvalWizardSave=true";
	postData += "&eval_update=false";
		postData += "&IE=TRUE";
	//postData += "&return_module="+return_module;
	//postData += "&return_action="+return_action;
	if (YAHOO.util.Selector.query('input[name^=P1_Partnerscontact]:checked')[0] != null) {
		postData += "&P1_Partnerscontact_id="+P1_Partnerscontact_id;
    }
	//alert(postData);
	var request = YAHOO.util.Connect.asyncRequest('POST', sUrl, callbackEvalWiz, postData);
}

/******************* END EVAL CREATE WIZARD CODE *****************/
var handleSuccessWiz = function(o){
	var formContents = document.getElementById('formContentsWiz');
	formContents.innerHTML = "";
	if(o.responseText !== undefined){
		response = o.responseText;
		if(!detectMIE()) {
			newForm = document.createElement("form");
			newForm.setAttribute("action","index.php?entryPoint=assignWizardSave");
			newForm.setAttribute("method","POST");
			newForm.setAttribute("name","assignWizardForm");
			newForm.setAttribute("id","assignWizardForm");
			formContents.appendChild(newForm);
			newDiv = document.createElement("div");
			newDiv.setAttribute("id","wizardDiv");
			newDiv.innerHTML = response;
			newForm.appendChild(newDiv);
		} else {
			newDiv = document.createElement("div");
			newDiv.setAttribute("id","wizardDiv");
			newDiv.innerHTML = response;
			formContents.appendChild(newDiv);
		}
		{/literal}
		{$tiny}
		{$tiny_contactemail}
		{literal}

	}
	YAHOO.util.Event.addListener('assign_wiz_hide', 'click', YAHOO.example.container.panel2.hide, YAHOO.example.container.panel2, true);
};

var handleFailureWiz = function(o){

	if(o.responseText !== undefined){
		document.getElementById('formContentsWiz').innerHTML = "Unable to contact server";
	}
};


var callbackWiz =
{
  success:handleSuccessWiz,
  failure:handleFailureWiz,
  argument:['foo','bar'],
  cache:false
};

function postAssignWizard() {		
	sUrl = "index.php?module=P1_Partners&action=assignwizard";
	var formObject = document.getElementById('MassUpdate');
	YAHOO.util.Connect.setForm(formObject); 
	var cObj = YAHOO.util.Connect.asyncRequest('POST', sUrl, callbackWiz);
	var container = document.getElementById('container');
	container.style.display = "block";
}




		YAHOO.namespace("example.container");

		function init() {
			// Instantiate a Panel from markup
			YAHOO.example.container.panel3 = new YAHOO.widget.Panel("panel3", { zIndex:"10000",width:"700px",visible:false, constraintoviewport:true,underlay: "shadow", modal: true, fixedcenter: true, close: false} );
			YAHOO.example.container.panel3.render();
			
			YAHOO.example.container.panel1 = new YAHOO.widget.Panel("panel1", { zIndex:"10000",width:"700px",visible:false, constraintoviewport:true,underlay: "shadow", modal: true, fixedcenter: true, close: false} );
			YAHOO.example.container.panel1.render();
			
			YAHOO.example.container.panel2 = new YAHOO.widget.Panel("panel2", { zIndex:"10000",width:"800px",visible:false, constraintoviewport:true,underlay: "shadow", modal: true, fixedcenter: true, close: false} );
			YAHOO.example.container.panel2.render();


		YAHOO.util.Event.addListener('quick_edit_hide', 'click', YAHOO.example.container.panel1.hide, YAHOO.example.container.panel1, true);
		YAHOO.util.Event.addListener('assign_button', 'click', YAHOO.example.container.panel2.show, YAHOO.example.container.panel2, true);
		YAHOO.util.Event.addListener('assign_wiz_hide', 'click', YAHOO.example.container.panel2.hide, YAHOO.example.container.panel2, true);
		YAHOO.util.Event.addListener('assign_wiz_hide1', 'click', YAHOO.example.container.panel2.hide, YAHOO.example.container.panel2, true);
		YAHOO.util.Event.addListener('assign_wiz_hide2', 'click', YAHOO.example.container.panel2.hide, YAHOO.example.container.panel2, true);
		YAHOO.util.Event.addListener('eval_wiz_hide', 'click', YAHOO.example.container.panel3.hide, YAHOO.example.container.panel3, true);
		}

		var newOppDialog;
		var delay_in_checking_opps = 1; //Variable to track if a user is currently editing an opp.
		var time_in_milli_sc_to_check_for_opps = 60000; //Time specified in msc. 1 min.
		YAHOO.util.Event.addListener(window, "load", init);
		YAHOO.util.Event.addListener(window, "load", setInterval(checkForNewOpps,time_in_milli_sc_to_check_for_opps)); 
		YAHOO.util.Event.addListener(window, "load", setupNewOppsDialogue);

/**
*  Setup the dialogue to notify users if a new opportunity has arrived.  Set it up once so we can re-use it.
*/
function setupNewOppsDialogue()
{
	newOppDialog = new YAHOO.widget.SimpleDialog("dlg", { 
		width: "20em", 
		effect:{effect:YAHOO.widget.ContainerEffect.FADE,duration:0.25}, 
		fixedcenter:true, modal:true, visible:false, draggable:false });
		newOppDialog.setHeader("Notice");
		var alert_message = SUGAR.language.get('P1_Partners', 'LBL_NEW_OPPS_WAITING');
		newOppDialog.setBody(alert_message);
}

/**
*  Check to see if new opportunities have arrived for the current user while on the PRM listview.
*/
function checkForNewOpps()
{
	var is_user_editing = YAHOO.example.container.panel1.cfg.getProperty('visible');
	
	/**
	*  If a user is editing an opp, don't do the check.  Rather, increase the delay in last check so 
	*  we don't miss any opps.
	*/
	if(is_user_editing)
	{
		delay_in_checking_opps++;
		return;
	}
	
	//Only make the check if the popup window isn't showing.
	if( !newOppDialog.cfg.getProperty('visible') )
	{
		
		var d = new Date();
		var now = d.getTime() - ( time_in_milli_sc_to_check_for_opps * delay_in_checking_opps); //Subtract time from last check.
		var query = 'entryPoint=checkForSixtyMinuteOpps&last_check_date=' + now;
		var callback_save = function(os) 
		{
			var new_opps = YAHOO.lang.JSON.parse(os.responseText);
			
			if(new_opps > 0)
			{
				newOppDialog.render(document.body); 
				newOppDialog.show();
			}
		};
		
		delay_in_checking_opps = 1; //Reset
		YAHOO.util.Connect.asyncRequest('POST', 'index.php', {success:callback_save, failure: function(o){}}, query);
	}
}

function toggleContainer(){
var container = document.getElementById('container');
var formContents = document.getElementById('formContents');
var formContentsWiz = document.getElementById('formContentsWiz');
var formContentsEvalWiz = document.getElementById('formContentsEvalWiz');
	if (container.style.display == "none") {
		container.style.display = "block";
	} else {
		container.style.display = "none";
		formContents.innerHTML = "";
		formContentsWiz.innerHTML = "";
	}
}
{/literal}
</script>

<div id="container" style="display: none;">
	
	<div id="panel1">
		<div class="hd">Fast Edit: oppQ<a class="container-close" href="javascript: void(0);" id="quick_edit_hide" onclick="toggleContainer();">Close</a></div>
		<div class="bd" id="formContents" style="height:600px;overflow: auto;"></div>
	</div>

	<div id="panel2">
		<div class="hd">Opportunity Wizard<a class="container-close" href="javascript: void(0);" id="assign_wiz_hide2" onclick="toggleContainer();">Close</a></div>
		<div class="bd" id="formContentsWiz" style="height:600px;overflow: auto;"></div>
	</div>
	
	<div id="panel3">
		<div class="hd">Evaluation Wizard<a class="container-close" href="javascript: void(0);" id="eval_wiz_hide" onclick="toggleContainer();">Close</a></div>
		<div class="bd" id="formContentsEvalWiz" style="height:410px;overflow: auto;"></div>
	</div>
</div>
{literal}
<script type="text/javascript">
<!--
function showdiv() {
	if(document.getElementById('Opportunitiessales_stage').value == 'Closed Lost') {
		var callback = {
                success: function(o) {
                	document.getElementById('div_info').innerHTML = o.responseText;
                	document.getElementById('formContents').style.overflow = "scroll";
			checkOppClosedReasonDependentDropdown('closed_lost_reason_detail_c',false,'','Opportunities');
					
                }
        	} 
        	var connectionObject = YAHOO.util.Connect.asyncRequest ('GET', 'index.php?module=P1_Partners&action=getclosedlost', callback);
	}
	else {
		document.getElementById('div_info').innerHTML = "";
	}
}

function validation() {
	if(document.getElementById('Opportunitiesname').value == "") {
                alert('Missing required field: Opportunity Name');
                document.getElementById('Opportunitiesname').focus();
                return false;
        }
	if(document.getElementById('Accountsname').value == "") {
                alert('Missing required field: Account Name');
                document.getElementById('Accountsname').focus();
                return false;
        }
	if(document.getElementById('Opportunitiesopportunity_type').value == "") {
                alert('Missing required field: Opportunity Type');
                document.getElementById('Opportunitiesopportunity_type').focus();
                return false;
        }
	if(document.getElementById('Opportunitiesdate_closed').value == "") {
                alert('Missing required field: Expected Close Date');
                document.getElementById('Opportunitiesdate_closed').focus();
                return false;
        }
	if(document.getElementById('Opportunitiessales_stage').value == "") {
                alert('Missing required field: Sales Stage');
                document.getElementById('Opportunitiessales_stage').focus();
                return false;
        }
	if(document.getElementById('Opportunitiesamount').value == "") {
                alert('Missing required field: Amount');
                document.getElementById('Opportunitiesamount').focus();
                return false;
        }
	if(document.getElementById('Opportunitiesusers').value == "") {
                alert('Missing required field: Subscriptions');
                document.getElementById('Opportunitiesusers').focus();
                return false;
        }
	if(document.getElementById('Opportunitiessales_stage').value == 'Closed Lost'){ 
		if( typeof(document.getElementById('closed_lost_reason_c')) !='undefined' 
		    && (document.getElementById('closed_lost_reason_c').value == "" 
	  	    || document.getElementById('closed_lost_reason_c').value == "None"))
		{
                	alert('Missing required field: Closed Lost Reason');
	                document.getElementById('closed_lost_reason_c').focus();
	                return false;
		}else if( typeof(document.getElementById('closed_lost_reason_c')) !='undefined' 
			 && document.getElementById('closed_lost_description').value == "") 
		{
                	alert('Missing required field: Closed Lost Description');
	                document.getElementById('closed_lost_description').focus();
	                return false;
		}else if (document.getElementById('closed_lost_reason_c').value == 'Competitor'
			&& document.getElementById('primary_reason_competitor_c').value == '')
		{
			alert('Missing required field: Primary Reason for Competitor');
			document.getElementById('primary_reason_competitor_c').focus();
			return false;
		}
        }
    /**
     * Begin Customization - jwhitcraft - ITR: 13820 - Remove the Validation fro the Current SOlution and Competitor 1
	if(document.getElementById('Opportunitiescurrent_solution').value == "") {
                alert('Missing required field: Current Solution');
                document.getElementById('Opportunitiescurrent_solution').focus();
                return false;
        }
    if(document.getElementById('Opportunitiescompetitor_1').value == "") {
                alert('Missing required field: Competitor 1');
                document.getElementById('Opportunitiescompetitor_1').focus();
                return false;
        }
    **/
	if(document.getElementById('Notesdescription').value != "" && document.getElementById('Notessubject').value == "") {
		alert('Please enter a Note Subject with the Note Description');
		document.getElementById('Notessubject').focus();
		return false;
	}
	
	if(!detectMIE()) {
		var qeForm = document.getElementById('quickEditForm');
		qeForm.submit();
	} else {
		submitQE();
	}
}



var handleSuccessQE = function(o){
	window.location.href = "index.php?module=P1_Partners&action=index";
};

var handleFailureQE = function(o){
	alert("Unable to contact server");
};

var callbackQE =
{
  success:handleSuccessQE,
  failure:handleFailureQE
};

var sUrl = "index.php";

function submitQE(){

	var Opportunitiesname = document.getElementById('Opportunitiesname').value;
	var Accountsname = document.getElementById('Accountsname').value;
	var Opportunitiesopportunity_type = document.getElementById('Opportunitiesopportunity_type').value;
	var Opportunitiesdate_closed = document.getElementById('Opportunitiesdate_closed').value;
	var Opportunitiessales_stage = document.getElementById('Opportunitiessales_stage').value;
	if(Opportunitiessales_stage.value == 'Closed Lost' && document.getElementById('Opportunitiesclosed_lost_description').value == "") {
	var Opportunitiesclosed_lost_description = document.getElementById('Opportunitiesclosed_lost_description').value;
	}
	var Opportunitiesamount = document.getElementById('Opportunitiesamount').value;
	var Opportunitiesusers = document.getElementById('Opportunitiesusers').value;
	var Opportunitiescurrent_solution = document.getElementById('Opportunitiescurrent_solution').value;
	var Opportunitiescompetitor_1 = document.getElementById('Opportunitiescompetitor_1').value;
	var Notessubject = escape(document.getElementById('Notessubject').value);
	var Notesdescription = escape(document.getElementById('Notesdescription').value);
	var Opportunitiesnext_step_due_date = document.getElementById('Opportunitiesnext_step_due_date').value;
	var Opportunitiescompetitor_2 = document.getElementById('Opportunitiescompetitor_2').value;
	var Opportunitiesnext_step = escape(document.getElementById('Opportunitiesnext_step').value);
	var Opportunitiesdescription = escape(document.getElementById('Opportunitiesdescription').value);
	var P1_PartnersQuickEditSave = document.getElementById('P1_PartnersQuickEditSave').value;
	var Opportunitiesid = document.getElementById('Opportunitiesid').value;
	var Opportunitiessixtymin_opp_c = document.getElementById('Opportunitiessixtymin_opp_c').value;
	var closedlostreason = document.getElementById('closed_lost_reason_c').value;
	var closedlostreasondetail = document.getElementById('closed_lost_reason_detail_c').value;
	var closedlostdescription = document.getElementById('closed_lost_description').value;
	var module = document.getElementById('module').value;
	var action = document.getElementById('action').value;
	var return_module = document.getElementById('return_module').value;
	var return_action = document.getElementById('return_action').value;
	
	var postData = "Opportunitiesname="+Opportunitiesname;
	postData += "&entryPoint=quickEditSave";
	postData += "&Accountsname="+Accountsname;
	postData += "&Opportunitiesopportunity_type="+Opportunitiesopportunity_type;
	postData += "&Opportunitiesdate_closed="+Opportunitiesdate_closed;
	postData += "&Opportunitiessales_stage="+Opportunitiessales_stage;
	if(Opportunitiessales_stage.value == 'Closed Lost' && document.getElementById('Opportunitiesclosed_lost_description').value == "") {
	postData += "&Opportunitiesclosed_lost_description="+Opportunitiesclosed_lost_description;
	}
	postData += "&Opportunitiesamount="+Opportunitiesamount;
	postData += "&Opportunitiesusers="+Opportunitiesusers;
	postData += "&Opportunitiescurrent_solution="+Opportunitiescurrent_solution;
	postData += "&Opportunitiescompetitor_1="+Opportunitiescompetitor_1;
	postData += "&Notessubject="+Notessubject;
	postData += "&Notesdescription="+Notesdescription;
	postData += "&Opportunitiesnext_step_due_date="+Opportunitiesnext_step_due_date;
	postData += "&Opportunitiescompetitor_2="+Opportunitiescompetitor_2;
	postData += "&Opportunitiesnext_step="+Opportunitiesnext_step;
	postData += "&Opportunitiesdescription="+Opportunitiesdescription;
	postData += "&closed_lost_reason_c="+closedlostreason;
	postData += "&closed_lost_reason_detail_c="+closedlostreasondetail;
	postData += "&closed_lost_description="+closedlostdescription;

	//hidden values
	postData += "&P1_PartnersQuickEditSave="+P1_PartnersQuickEditSave;
	postData += "&Opportunitiesid="+Opportunitiesid;
	postData += "&Opportunitiessixtymin_opp_c="+Opportunitiessixtymin_opp_c;
	postData += "&module="+module;
	postData += "&action="+action;
	postData += "&return_module="+return_module;
	postData += "&return_action="+return_action;

	
	//alert(postData);
	var request = YAHOO.util.Connect.asyncRequest('POST', sUrl, callbackQE, postData);
}
-->
</script>
{/literal}



{* $QSJAVASCRIPT *}

{literal}
<script type="text/javascript">
<!--
var b_version = navigator.appVersion;
var version=parseFloat(b_version);
//enableQS(false);
function detectMIE()
{
	var browser = navigator.appName;
	var version = navigator.appVersion;
	if(browser=="Microsoft Internet Explorer"){
		return true;
	} else {
		return false;
	}
}
function showContacts() {
	var account_id = document.getElementById('P1_Partnerspartner_assigned_to_c').value;
	var callback = {
        	success: function(o) {
               	document.getElementById('div_info').innerHTML = o.responseText;
               	}
   	} 
       	var connectionObject = YAHOO.util.Connect.asyncRequest ('GET', 'index.php?module=P1_Partners&action=getcontacts&eval_flag=&account_id='+account_id, callback);
}
function validationWiz() {
	if(document.getElementById('P1_Partnerspartner_assigned_to_c').value == ""){
		alert('Please select a Partner Account Name');
		document.getElementById('P1_Partnerspartner_assigned_to_c').focus();
		return false;
	}
	
	if(!detectMIE()) {
		var P1_Partnerscontact_id = document.assignWizardForm.P1_Partnerscontact_id;
	} else {
        // begin fix - jwhitcraft - ITR: 14024 - make IE select all radio boxes on the form
		var P1_Partnerscontact_id = document.getElementsByName("P1_Partnerscontact_id");
        // end fix
	} 
	myOption = -1;
        var P1_Partnerscontact_id_length = P1_Partnerscontact_id.length;
        if(P1_Partnerscontact_id_length == undefined) {
            if(!P1_Partnerscontact_id.checked) {
        		alert("Please select a Partner Contact");
                	return false;
            }
        }
       	else {
        	for (i=P1_Partnerscontact_id_length-1; i > -1; i--) {
                	if(P1_Partnerscontact_id[i].checked) {
                        	myOption = i;
                        	i = -1;
                	}
        	}
        	if (myOption == -1) {
                	alert("Please select a Partner Contact");
                	return false;
        	}
        }

	if(document.getElementById('P1_Partnersemail_subject').value == "") {
		alert('Please enter a Subject');
                document.getElementById('P1_Partnersemail_subject').focus();
                return false;
	}
	if(document.getElementById('P1_Partnersbody_html').value == "") {
		alert('Please enter content for the email');
                document.getElementById('P1_Partnersbody_html').focus();
                return false;
	}
	
	if(!detectMIE()) {
		var wizForm = document.getElementById('assignWizardForm');
		wizForm.submit();
	} else {
		submitWizard();
	}
}


var handleSuccessSW = function(o){
	window.location.href = "index.php?module=P1_Partners&action=index";
};

var handleFailureSW = function(o){
	alert("Unable to contact server");
};

var callbackSW =
{
  success:handleSuccessSW,
  failure:handleFailureSW
};

var sUrl = "index.php";

function submitWizard(){

	var P1_Partnersemail_subject = document.getElementById('P1_Partnersemail_subject').value;
    // begin fix - jwhitcraft - ITR: 14024 - Make sure you get the checked value instead of just the first value
    var P1_Partnerscontact_id = YAHOO.util.Selector.query('#div_info input[type=radio]:checked')[0].value;
    // end fix
	var P1_Partnerspartner_assigned_to_c = document.getElementById('P1_Partnerspartner_assigned_to_c').value;
	var P1_Partnersopp_ids = document.getElementById('P1_Partnersopp_ids').value;
	var P1_Partnersuser_email = document.getElementById('P1_Partnersuser_email').value;
	var P1_PartnersAssignWizardSave = document.getElementById('P1_PartnersAssignWizardSave').value;
	var return_module = document.getElementById('return_module').value;
	var return_action = document.getElementById('return_action').value;
	var P1_Partnersbody_html = document.getElementById('P1_Partnersbody_html').value;
	
	var postData = "P1_Partnersemail_subject="+P1_Partnersemail_subject;
	postData += "&entryPoint=assignWizardSave";
	postData += "&P1_Partnerscontact_id="+P1_Partnerscontact_id;
	postData += "&P1_Partnerspartner_assigned_to_c="+P1_Partnerspartner_assigned_to_c;
	postData += "&P1_Partnersopp_ids="+P1_Partnersopp_ids;
	postData += "&P1_Partnersuser_email="+P1_Partnersuser_email;
	postData += "&P1_PartnersAssignWizardSave="+P1_PartnersAssignWizardSave;
	postData += "&return_module="+return_module;
	postData += "&return_action="+return_action;
	postData += "&P1_Partnersbody_html="+P1_Partnersbody_html;
	
	//alert(postData);
	var request = YAHOO.util.Connect.asyncRequest('POST', sUrl, callbackSW, postData);
}
-->
</script>
{/literal}

