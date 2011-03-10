//FILE SUGARCRM flav=pro ONLY
/**
 * Javascript for Quotes
 *
 * LICENSE: The contents of this file are subject to the SugarCRM Professional
 * End User License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You
 * may not use this file except in compliance with the License.  Under the
 * terms of the license, You shall not, among other things: 1) sublicense,
 * resell, rent, lease, redistribute, assign or otherwise transfer Your
 * rights to the Software, and 2) use the Software for timesharing or service
 * bureau purposes such as hosting the Software for commercial gain and/or for
 * the benefit of a third party.  Use of the Software may be subject to
 * applicable fees and any use of the Software without first paying applicable
 * fees is strictly prohibited.  You do not have the right to remove SugarCRM
 * copyrights from the source code or user interface.
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
 * by SugarCRM are Copyright (C) 2005 SugarCRM, Inc.; All Rights Reserved.
 */

// $Id: quotes.js 56510 2010-05-17 18:54:49Z jenny $

var count = 0;
function openPopup(value, count){
	var popup_request_data = {
		"call_back_function" : "set_product_return",
		"form_name" : "EditView",
		"field_to_name_array" : {
			"id" : "id",
			"name" : "name",
			"cost_usdollar" : "cost_usdollar",
			"list_usdollar" : "list_usdollar",
			"discount_usdollar" : "discount_usdollar",
			"mft_part_num" : "mft_part_num",
			"pricing_factor" : "pricing_factor",
			"type_id" : "type_id",
			"tax_class" : "tax_class",
			"tax_class_name" : "tax_class_name",
			"description":"description"
			
		},
		"passthru_data" : {
			"row_id" : count
		}
	};
	open_popup('ProductTemplates', 600, 400, '&tree=ProductsProd&name='+value, true, false, popup_request_data);
}
function toReadOnly(doc, count){
	if (doc.getElementById('product_template_id_' +  count ).value != '')
	{
		toggleTaxSelect(doc, count, true);
	    setToReadOnly(doc.getElementById('cost_price_' +  count ));
		setToReadOnly(doc.getElementById('list_price_'+count));
		//setToReadOnly(doc.getElementById('discount_price_'+ count ));
		setToReadOnly(doc.getElementById('tax_class_name_'+ count ));
		setToReadOnly(doc.getElementById('mft_part_num_'+ count ));
		//setToReadOnly(doc.getElementById('pricing_formula_name_'+count));
	}else{
		toEdit(doc, count);
	}
}
function setToReadOnly(element){
	element.style.background = '#dddddd';
	element.readOnly = true;
}
function toEdit(doc, count){
	toggleTaxSelect(doc, count, false);
	setToEdit(doc.getElementById('cost_price_' +  count ));
	setToEdit(doc.getElementById('list_price_'+count));
	setToEdit(doc.getElementById('discount_price_'+ count ));
	setToEdit(doc.getElementById('tax_class_name_'+ count ));
	setToEdit(doc.getElementById('mft_part_num_'+ count ));
	//setToEdit(doc.getElementById('pricing_formula_name_'+count));

}

function setToEdit(element){
	element.style.background = '#ffffff';
	element.readOnly = false;
}

function taxSelectChanged(doc, count){
	doc.getElementById('tax_class_' + count).value = doc.getElementById('tax_class_select_name_' + count).options[doc.getElementById('tax_class_select_name_' + count).selectedIndex].value;
	calculate(doc);
}
/*function priceSelectChanged(doc, count){
	doc.getElementById('pricing_formula_' + count).value = doc.getElementById('pricing_formula_select_name_' + count).options[doc.getElementById('pricing_formula_select_name_' + count).selectedIndex].value;
	calculate(doc);
}*/
function selectTax(doc, count){
		if(doc.getElementById('tax_class_name_' + count).value != ''){
			for(var i = 0; i < doc.getElementById('tax_class_select_name_' + count).options.length; i++){
				if(doc.getElementById('tax_class_select_name_' + count).options[i].value == 	doc.getElementById('tax_class_name_' + count).value){
					doc.getElementById('tax_class_select_name_' + count).selectedIndex = i;
					return;
				}
			}
		}
}

function discount_calculated(doc, count){
    var discountAmount;
	if(doc.getElementById('checkbox_select_' + count).checked == true){
		doc.getElementById('discount_select_' + count).value = true;
		discountAmount= unformatNumber(doc.getElementById('discount_amount_' + count).value, num_grp_sep, dec_sep)/100*unformatNumber(doc.getElementById('discount_price_' + count).value, num_grp_sep, dec_sep);
	}
	else{
		doc.getElementById('discount_select_' + count).value = false;
		discountAmount= unformatNumber(doc.getElementById('discount_amount_' + count).value, num_grp_sep, dec_sep);
	}
		doc.getElementById('deal_calc_' + count).value = discountAmount;
		calculate(doc);
}

var holding_row_id = '';

function copy_cell_children(from_cell, to_cell){
	
}

function ungrab_table_row(){

	if(holding_row_id != ''){
		
		var from_row = document.getElementById(holding_row_id);
		if(typeof(from_row) == 'undefined' || !from_row){
			holding_row_id = '';
			return;
		}
		holding_row_id = '';
		from_row.style.background = '';
	}
}
function grab_table_row(row_id){
	if(holding_row_id == ''){
		holding_row_id = row_id;
		var from_row = document.getElementById(holding_row_id);
		from_row.style.background = '#666666';
	}else{
		var from_row = document.getElementById(holding_row_id);
		if(typeof(from_row) == 'undefined' || !from_row){
			holding_row_id = '';
			return;
		}
		var from_table = document.getElementById(from_row.tableId);
		if(typeof(from_table) == 'undefined' || !from_table){
			holding_row_id = '';
			return;
		}
		var from_body = from_table.tBodies[0];
		var from_index = from_row.rowIndex;
		var from_count = from_row.count;
		from_row.style.background = '';
		var to_row = document.getElementById(row_id);
		to_row.style.background = '';
		var to_table = document.getElementById(to_row.tableId);
		var to_body = to_table.tBodies[0];
		var to_index = to_row.rowIndex;
		var to_count = to_row.count;
		if(to_index < from_index  ){
			temp_id = holding_row_id;
			holding_row_id = row_id;
			return grab_table_row(temp_id)
		}
		
		var to_offset = 0;
		if(to_table != from_table){
			to_offset++;
		}

		
		
		//swap the table id's the use for getting table information
		var tempId = from_row.tableId;
		from_row.tableId = to_row.tableId;
		to_row.tableId = tempId;
		//handle the inserts
		lookup_item('parent_group_' + to_count , document).value = to_row.tableId;
		lookup_item('parent_group_' + from_count, document).value = from_row.tableId;
		lookup_item('parent_group_index_' + from_count, document).value = to_index;
		lookup_item('parent_group_index_' + to_count, document).value = from_index;
		
		var from_position = lookup_item('parent_group_position_' + from_count, document).value;
		var to_position = lookup_item('parent_group_position_' + to_count, document).value;
		lookup_item('parent_group_position_' + from_count, document).value = to_position;
		lookup_item('parent_group_position_' + to_count, document).value = from_position;
		
		
		//alert('t0 ' +to_row.tableId + ' == ' + lookup_item('parent_group_' + to_count , document).value);
		to_body.insertBefore( from_row, to_row);
		
		var insertTo = to_index + to_offset;
		var insertFrom = from_index;
		if(insertTo >= to_body.rows.length)
			insertTo = to_body.rows.length - 1;
		if(insertFrom >= from_body.rows.length){
			insertFrom = from_body.rows.length - 1;
			
		}
		
		

		if(insertFrom == 0){
			from_body.appendChild(to_body.rows[insertTo]);
		}else{
			from_body.insertBefore(to_body.rows[insertTo], from_body.rows[insertFrom]);
		}
		holding_row_id = '';
		
		//recalulate
		calculate(document);
	}
}
function toggleTaxSelect(doc, count, hideselect){
		if(hideselect){
			doc.getElementById('taxselect' + count).style.display = 'none';
			doc.getElementById('taxinput' + count).style.display = 'inline';
			//doc.getElementById('priceselect' + count).style.display = 'none';
			//doc.getElementById('priceinput' + count).style.display = 'inline';
			
			
		}else{
			doc.getElementById('taxselect' + count).style.display = 'inline';
			selectTax(doc, count);
			//priceSelectChanged(doc, count);
			doc.getElementById('taxinput' + count).style.display = 'none';
			//doc.getElementById('priceselect' + count).style.display = 'inline';
			//doc.getElementById('priceinput' + count).style.display = 'none';
			doc.getElementById('tax_class_' + count).value = doc.getElementById('tax_class_select_name_' + count).options[doc.getElementById('tax_class_select_name_' + count).selectedIndex].value;
		}
}
	var tax_rate_keys = new Array();
	var tax_rates = new Array();
	function add_tax_class(name, value){
		tax_rate_keys.push(name);
		tax_rates[name] = value;
	}
	
		
	var item_list_MSI = new Array();
	function lookup_item(id, doc){
			if(typeof(item_list_MSI[id]) != 'undefined'){
				return item_list_MSI[id];
			}
			return doc.getElementById(id);
	}
	var default_product_status = 'UNSET';
	var invalidAmount = 'UNSET';
	var selectButtonTitle = 'UNSET';
	var selectButtonKey = 'UNSET';
	var selectButtonValue = 'UNSET';
	var deleteButtonName = 'UNSET';
	var deleteButtonConfirm = 'UNSET';
	var deleteGroupConfirm = 'UNSET';
	var deleteButtonValue = 'UNSET';
	var addRowName = 'UNSET';
	var addRowValue = 'UNSET';
	var deleteTableName = 'UNSET';
	var deleteTableValue = 'UNSET';
	var subtotal_string = 'UNSET';
	var shipping_string = 'UNSET';
	var deal_tot_string = 'UNSET';
	var new_sub_string = 'UNSET';
	var total_string = 'UNSET';
	var tax_string = 'UNSET';
	var addGroupName = 'UNSET';
	var addGroupValue = 'UNSET';
	
	var addCommentName = 'UNSET';
	var addCommentValue = 'UNSET';
	var deleteCommentName = 'UNSET';
	var deleteCommentValue = 'UNSET';
	var deleteCommentConfirm = 'UNSET';
	var list_quantity_string = 'UNSET';
	var list_product_name_string = 'UNSET';
	var list_mf_part_num_string = 'UNSET';
	var list_taxclass_string = 'UNSET';
	var list_cost_string = 'UNSET';
	var list_list_string = 'UNSET';
	var list_discount_string = 'UNSET';
	var list_deal_tot = 'UNSET';
	var check_data = 'UNSET';
	
	var table_list = new Array();
	var blankDataLabel = document.createElement('td');
			blankDataLabel.className = 'dataLabel';
			blankDataLabel.width = 90;
			blankDataLabel.nowrap = true;	
	
	function deleteTable(id){
        table_array[id].splice(id,1);
		ungrab_table_row()
		lookup_item('delete_table_' + id, document).value = id;
		var table = lookup_item(id, document);
		var table_tally = lookup_item(id + '_tally', document);
		var table_header =  lookup_item( id+ '_header', document);
		var tables = document.getElementById('add_tables');
		tables.removeChild(table);
		tables.removeChild(table_tally);
		calculate(document);
		tables.removeChild(table_header);
		tables.removeChild(lookup_item( id+ '_hr1', document));
		tables.removeChild(lookup_item( id+ '_hr2', document));
		
	}
	
	table_array = new Array();
	rows_nb_per_group = new Array();
	
	function addTable(id, bundle_stage, bundle_name, bundle_shipping){
			if(id == ''){
				id = 'group_' + count;
			}
			table_array[id]=new Array();
			rows_nb_per_group[id]=1;
			var form = document.getElementById('EditView');
			var textEl = document.createElement('input');
			textEl.setAttribute('type', 'hidden')
			textEl.setAttribute('name',  "delete_table[" + id + "]" );
			textEl.setAttribute('id', "delete_table_" + id  );
			textEl.value = 1;
			item_list_MSI["delete_table[" + id + "]"] = textEl;
			form.appendChild(textEl);
			
			
			var tables = document.getElementById('add_tables');
			var tableEl = document.createElement('table');
			tableEl.setAttribute('name',  id );
			tableEl.setAttribute('id', id );
			tableEl.border = 0;
			tableEl.cellspacing = 1;
			tableEl.cellpadding = 0;
			item_list_MSI[id] = tableEl;
			var newHR = document.createElement('hr');
			newHR.id = id + '_hr1';
			var newDIV = document.createElement('div');
			newDIV.id = id + '_header';
			newDIV.innerHTML = getTableSettings(id);
			tables.appendChild(newHR);
			tables.appendChild(newDIV);
			tables.appendChild(tableEl);
			table_list.push(id);
			var newHR = document.createElement('hr');
			newHR.id = id + '_hr2';
			tables.appendChild(newHR);
			
			var tableEl = document.createElement('table');
			tableEl.setAttribute('name',  id + '_tally' );
			tableEl.setAttribute('id', id + '_tally' );
			tableEl.border = 0;
			tableEl.cellspacing = 1;
			tableEl.cellpadding = 0;
			item_list_MSI[id + '_tally'] = tableEl;
			
			tables.appendChild(tableEl);
			addTableTally(id);
			addTableHeader(id);
			setTableSettingsValue(id, bundle_stage, bundle_name, bundle_shipping);
			count++;
			return id;

	}
	
	function table_exists(id){
		for(i = 0; i < table_list.length; i++){
			if(table_list[i] == id){
				return true;
			}
		}
		return false;
	}
	
	function setTableSettingsValue(id, stage_val, name_val, shipping_val){
		var select = document.getElementById('bundle_stage_' + id);
		for(var m = 0; m < select.options.length; m++){
			if(select.options[m].value == stage_val){
				select.options[m].selected = true;
			}
		}
		var name = document.getElementById('bundle_name_' + id);
		name.value = name_val;
		var shipping = document.getElementById('shipping_' + id);
		shipping.value = formatNumber(shipping_val, num_grp_sep, dec_sep, precision, precision);
	}
	
	
	function getTableSettings(id){
			var temp_html = document.getElementById('ie_hack_stage').innerHTML;
			temp_html = temp_html.replace('select_id', 'bundle_stage_' + id);
			temp_html = temp_html.replace('select_name', 'bundle_stage[' + id + ']');
			temp_html = temp_html.replace('name_id', 'bundle_name_' + id);
			temp_html = temp_html.replace('name_name', 'bundle_name[' + id + ']');
			temp_html = temp_html.replace('table_id', 'bundle_header_' + id);
			temp_html = temp_html.replace('table_name', 'bundle_header[' + id + ']');
			return temp_html;
			
			
	}
	
	function addTableTally(id){
			
			var tableEl = document.getElementById(id + '_tally');
			var rowEl = tableEl.insertRow(tableEl.rows.length);
			var tdEl = blankDataLabel.cloneNode(false);
				tdEl.width = '550';
			rowEl.appendChild(tdEl);
			var inputEl = document.createElement('input');
				inputEl.className = 'button';
				inputEl.type = 'button';
				inputEl.tableId = id;
				inputEl.onclick = function(){
						addRow("","","","",0,0,"","","","","","","", this.tableId, '', '', '', '', '','0','','', '');
				}
				inputEl.name = addRowName;
				inputEl.value = addRowValue;

			tdEl.appendChild(inputEl);
			
			var inputEl = document.createElement('input');
				inputEl.className = 'button';
				inputEl.type = 'button';
				inputEl.tableId = id;
				inputEl.onclick = function(){
						addCommentRow("", this.tableId, "");
				}
				inputEl.name = addCommentName;
				inputEl.value = addCommentValue;
			
			tdEl.appendChild(inputEl);
			var tdEl = blankDataLabel.cloneNode(false);
				tdEl.appendChild(document.createTextNode(subtotal_string));
			rowEl.appendChild(tdEl);
			var tdEl = blankDataLabel.cloneNode(false);
				tdEl.width = 70;
			rowEl.appendChild(tdEl);
			var inputEl = document.createElement('input');
				inputEl.type = 'text';
				inputEl.size = 15;
				inputEl.tabIndex = 6;
				inputEl.name = 'subtotal[' + id + ']';
				inputEl.id = 'subtotal_' + id;
				inputEl.readOnly = true;
				inputEl.style.textAlign = 'right';
				setToReadOnly(inputEl);
				tdEl.appendChild(inputEl);
				item_list_MSI['subtotal_' + id] = inputEl;
				rowEl.appendChild(tdEl);
			var inputEl = document.createElement('input');
				inputEl.type = 'hidden';
				inputEl.size = 15;
				inputEl.name = 'subtotal_usdollar[' + id + ']';
				inputEl.id = 'subtotal_usdollar' + id;
				inputEl.readOnly = true;
				tdEl.appendChild(inputEl);
				inputEl.style.textAlign = 'right';
				item_list_MSI['subtotal_usdollar' + id] = inputEl;
				rowEl.appendChild(tdEl);
			var tdEl = blankDataLabel.cloneNode(false);
			rowEl.appendChild(tdEl);
			
			var rowEl = tableEl.insertRow(tableEl.rows.length);
            var tdEl = blankDataLabel.cloneNode(false);
            tdEl.width = 550;
            rowEl.appendChild(tdEl);
            var tdEl = blankDataLabel.cloneNode(false);
                tdEl.appendChild(document.createTextNode(deal_tot_string));
            rowEl.appendChild(tdEl);
            var tdEl = blankDataLabel.cloneNode(false);
                tdEl.width = 70;
            rowEl.appendChild(tdEl);
            var inputEl = document.createElement('input');
                inputEl.type = 'text';
                inputEl.size = 15;
                inputEl.tabIndex = 6;
                inputEl.name = 'deal_tot[' + id + ']';
                inputEl.id = 'deal_tot_' + id;
                inputEl.onchange = function(){
                    if(isAmount(toDecimal(unformatNumber(this.value, num_grp_sep, dec_sep), precision))){ calculate(document); } else { alert (invalidAmount); this.select()} ;
                }   
                inputEl.readOnly = true;
                inputEl.style.textAlign = 'right';
				setToReadOnly(inputEl);
                item_list_MSI['deal_tot_' + id] = inputEl;
                tdEl.appendChild(inputEl);
            rowEl.appendChild(tdEl);

            var tdEl = blankDataLabel.cloneNode(false);
            rowEl.appendChild(tdEl);
			
			//Discounted Subtotal
			var rowEl = tableEl.insertRow(tableEl.rows.length);
            var tdEl = blankDataLabel.cloneNode(false);
            tdEl.width = 550;
            rowEl.appendChild(tdEl);
            var tdEl = blankDataLabel.cloneNode(false);
                tdEl.appendChild(document.createTextNode(new_sub_string));
            rowEl.appendChild(tdEl);
            var tdEl = blankDataLabel.cloneNode(false);
                tdEl.width = 70;
            rowEl.appendChild(tdEl);
            var inputEl = document.createElement('input');
                inputEl.type = 'text';
                inputEl.size = 15;
                inputEl.tabIndex = 6;
                inputEl.name = 'new_sub[' + id + ']';
                inputEl.id = 'new_sub_' + id;
                inputEl.onchange = function(){
                    if(isAmount(toDecimal(unformatNumber(this.value, num_grp_sep, dec_sep), precision))){ calculate(document); } else { alert (invalidAmount); this.select()} ;
                }   
                inputEl.readOnly = true;
                inputEl.style.textAlign = 'right';
				setToReadOnly(inputEl);
                item_list_MSI['new_sub_' + id] = inputEl;
                tdEl.appendChild(inputEl);
            rowEl.appendChild(tdEl);
       /*     var inputEl = document.createElement('input');
                inputEl.type = 'hidden';
                inputEl.size = 15;
                inputEl.name = 'shipping_usdollar[' + id + ']';
                inputEl.id = 'shipping_usdollar_' + id;
                inputEl.readOnly = true;
                inputEl.style.textAlign = 'right';
                tdEl.appendChild(inputEl);
            rowEl.appendChild(tdEl);*/
            var tdEl = blankDataLabel.cloneNode(false);
            rowEl.appendChild(tdEl);
			
			
			var rowEl = tableEl.insertRow(tableEl.rows.length);
			var tdEl = blankDataLabel.cloneNode(false);
			tdEl.width = 550;
			rowEl.appendChild(tdEl);
			var tdEl = blankDataLabel.cloneNode(false);
				tdEl.appendChild(document.createTextNode(tax_string));
			rowEl.appendChild(tdEl);
			var tdEl = blankDataLabel.cloneNode(false);
				tdEl.width = 70;
			rowEl.appendChild(tdEl);
			var inputEl = document.createElement('input');
				inputEl.type = 'text';
				inputEl.size = 15;
				inputEl.tabIndex = 6;
				inputEl.name = 'tax[' + id + ']';
				inputEl.id = 'tax_' + id;
				inputEl.readOnly = true;
				inputEl.style.textAlign = 'right';
				setToReadOnly(inputEl);
				tdEl.appendChild(inputEl);
			item_list_MSI['tax_' + id] = inputEl;
			rowEl.appendChild(tdEl);
			var inputEl = document.createElement('input');
				inputEl.type = 'hidden';
				inputEl.size = 15;
				inputEl.name = 'tax_usdollar[' + id + ']';
				inputEl.id = 'tax_usdollar_' + id;
				inputEl.readOnly = true;
				inputEl.style.textAlign = 'right';
				tdEl.appendChild(inputEl);
			rowEl.appendChild(tdEl);
			var tdEl = blankDataLabel.cloneNode(false);
			rowEl.appendChild(tdEl);
			
			
			
			
			var rowEl = tableEl.insertRow(tableEl.rows.length);
			var tdEl = blankDataLabel.cloneNode(false);
			tdEl.width = 550;
			rowEl.appendChild(tdEl);
			var tdEl = blankDataLabel.cloneNode(false);
				tdEl.appendChild(document.createTextNode(shipping_string));
			rowEl.appendChild(tdEl);
			var tdEl = blankDataLabel.cloneNode(false);
				tdEl.width = 70;
			rowEl.appendChild(tdEl);
			var inputEl = document.createElement('input');
				inputEl.type = 'text';
				inputEl.size = 15;
				inputEl.tabIndex = 6;
				inputEl.name = 'shipping[' + id + ']';
				inputEl.id = 'shipping_' + id;
				inputEl.onchange = function(){
					if(isAmount(toDecimal(unformatNumber(this.value, num_grp_sep, dec_sep), precision))){ calculate(document); } else { alert (invalidAmount); this.select()} ;
				}	
				inputEl.readOnly = false;
				inputEl.style.textAlign = 'right';
				item_list_MSI['shipping_' + id] = inputEl;
				tdEl.appendChild(inputEl);
			rowEl.appendChild(tdEl);
			var inputEl = document.createElement('input');
				inputEl.type = 'hidden';
				inputEl.size = 15;
				inputEl.name = 'shipping_usdollar[' + id + ']';
				inputEl.id = 'shipping_usdollar_' + id;
				inputEl.readOnly = true;
				inputEl.style.textAlign = 'right';
				tdEl.appendChild(inputEl);
			rowEl.appendChild(tdEl);
			var tdEl = blankDataLabel.cloneNode(false);
			rowEl.appendChild(tdEl);            
			
			
			var rowEl = tableEl.insertRow(tableEl.rows.length);
			var tdEl = blankDataLabel.cloneNode(false);
			tdEl.width = 550;
			rowEl.appendChild(tdEl);
			var inputEl = document.createElement('input');
				inputEl.className = 'button';
				inputEl.type = 'button';
				inputEl.tableId = id;
				inputEl.onclick = function(){
					   if (confirm(deleteGroupConfirm)) {
						deleteTable(this.tableId);
					   }
				}
				inputEl.name = deleteTableName;
				inputEl.value = deleteTableValue;
			
			tdEl.appendChild(inputEl);
			var tdEl = blankDataLabel.cloneNode(false);
				tdEl.appendChild(document.createTextNode(total_string));
			rowEl.appendChild(tdEl);
			var tdEl = blankDataLabel.cloneNode(false);
				tdEl.width = 70;
			var inputEl = document.createElement('input');
				inputEl.type = 'text';
				inputEl.size = 15;
				inputEl.tabIndex = 6;
				inputEl.name = 'total[' + id + ']';
				inputEl.id = 'total_' + id;
				inputEl.readOnly = true;
				inputEl.style.textAlign = 'right';
				setToReadOnly(inputEl);
				tdEl.appendChild(inputEl);
				item_list_MSI['total_' + id] = inputEl;
			rowEl.appendChild(tdEl);
			var tdEl = blankDataLabel.cloneNode(false);
				tdEl.width = 70;
			var inputEl = document.createElement('input');
				inputEl.type = 'hidden';
				inputEl.size = 15;
				inputEl.name = 'total_usdollar[' + id + ']';
				inputEl.id = 'total_usdollar_' + id;
				inputEl.style.textAlign = 'right';
				inputEl.readOnly = true;
				tdEl.appendChild(inputEl);
				rowEl.appendChild(tdEl);
			var tdEl = blankDataLabel.cloneNode(false);
			rowEl.appendChild(tdEl);
	}
	
	function addTableHeader(id){
			var tableEl = document.getElementById(id);
			var rowEl = tableEl.insertRow(tableEl.rows.length);
			var tdEl = blankDataLabel.cloneNode(false);
				tdEl.width = 1;
				tdEl.appendChild(document.createTextNode(''));
			rowEl.appendChild(tdEl);
			var tdEl = blankDataLabel.cloneNode(false);
				tdEl.width = 55;
				tdEl.appendChild(document.createTextNode(list_quantity_string));
			rowEl.appendChild(tdEl);
			var tdEl = blankDataLabel.cloneNode(false);
				tdEl.width = 200;
				tdEl.appendChild(document.createTextNode(list_product_name_string));
			rowEl.appendChild(tdEl);
			var tdEl = blankDataLabel.cloneNode(false);
			tdEl.width = 55;
			tdEl.appendChild(document.createTextNode(''));
			rowEl.appendChild(tdEl);
			var tdEl = blankDataLabel.cloneNode(false);
				tdEl.width = 77;
				tdEl.appendChild(document.createTextNode(list_mf_part_num_string));
			rowEl.appendChild(tdEl);
			var tdEl = blankDataLabel.cloneNode(false);
				tdEl.width = 100;
				tdEl.appendChild(document.createTextNode(list_taxclass_string));
			rowEl.appendChild(tdEl);
			var tdEl = blankDataLabel.cloneNode(false);
				tdEl.width = 75;
				tdEl.appendChild(document.createTextNode(list_cost_string));
			rowEl.appendChild(tdEl);	
			var tdEl = blankDataLabel.cloneNode(false);
				tdEl.width = 75;
				tdEl.appendChild(document.createTextNode(list_list_string));
			rowEl.appendChild(tdEl);		
			var tdEl = blankDataLabel.cloneNode(false);
				tdEl.appendChild(document.createTextNode(list_discount_string));
			rowEl.appendChild(tdEl);
            var tdEl = blankDataLabel.cloneNode(false);
			    tdEl.width = 75;
                tdEl.appendChild(document.createTextNode(list_deal_tot));
            rowEl.appendChild(tdEl);

	}
	
	
	function addCommentRow(id, table_id, comment_description ){
			var form = document.getElementById('EditView');
			var table = document.getElementById(table_id);
			var row = table.insertRow(table.rows.length );
			var rowName = 'item_row_' + count;
			row.setAttribute('valign', 'top');
			row.id = rowName;
			row.tableId = table.id;
			row.count = count;
			
			var cell = row.insertCell(row.cells.length);
			cell.nowrap = 'nowrap';
			var buttonEl = document.createElement('input');
			buttonEl.setAttribute('type', 'button');
			buttonEl.count = count;
			buttonEl.onclick= function(){grab_table_row('item_row_' + this.count);}
			buttonEl.setAttribute('name', '||');
			buttonEl.setAttribute('value', '||');
			buttonEl.className = 'button';
			cell.appendChild(buttonEl);			
			
			var cell = row.insertCell(row.cells.length);
			cell.colSpan="8";
			var textEl = document.createElement('input');
			textEl.setAttribute('type', 'hidden')
			textEl.setAttribute('name',  "comment_id[" + count + "]" );
			textEl.setAttribute('id', "comment_id_" + count );
			textEl.value = id;
			form.appendChild(textEl);			
			
			var textEl = document.createElement('textarea');
			textEl.setAttribute('rows', 3);
			textEl.setAttribute('cols',  120);
			textEl.count = count;
			comment_description = comment_description.replace(/&#039;/g, '\'');
			textEl.value = comment_description.replace(/<br>/g, '\n');			
			textEl.setAttribute('name', "comment_description[" + count + "]" );
			cell.appendChild(textEl);			
			
			var textEl = document.createElement('input');
			textEl.setAttribute('type', 'hidden')
			textEl.setAttribute('name',  "comment_index[" + count + "]" );
			textEl.setAttribute('id', "comment_index" + count );
			textEl.setAttribute('value', count);
			cell.appendChild(textEl);

			var textEl = document.createElement('input');
			textEl.setAttribute('type', 'hidden')
			textEl.setAttribute('name',  "comment_delete[" + count + "]" );
			textEl.setAttribute('id', "comment_delete_" + count);
			textEl.value = 1;
			form.appendChild(textEl);
			
			var textEl = document.createElement('input');
			textEl.setAttribute('type', 'hidden')
			textEl.setAttribute('name',  "parent_group[" + count + "]" );
			textEl.setAttribute('id', "parent_group_" + count );
			textEl.setAttribute('value', table_id);
			form.appendChild(textEl);		
			
			var textEl = document.createElement('input');
			textEl.setAttribute('type', 'hidden')
			textEl.setAttribute('name',  "parent_group_index[" + count + "]" );
			textEl.setAttribute('id', "parent_group_index" + count );			
			textEl.setAttribute('value', row.rowIndex);
			item_list_MSI["parent_group_index_"+ count] = textEl;
			cell.appendChild(textEl);	

			var textEl = document.createElement('input');
			textEl.setAttribute('type', 'hidden');
			textEl.setAttribute('name',  "parent_group_position[" + count + "]" );
			textEl.setAttribute('id', "parent_group_position" + count );			
			textEl.setAttribute('value', count);
			item_list_MSI["parent_group_position_"+ count] = textEl;
			cell.appendChild(textEl);	
			
			var cell = row.insertCell(row.cells.length);
			var buttonEl = document.createElement('input');
			buttonEl.setAttribute('type', 'button');
			buttonEl.setAttribute('id', 'delete_comment' + count);
			buttonEl.tableId = table_id;
			buttonEl.count = count;
			buttonEl.onclick= function(){
									if (confirm(deleteCommentConfirm)) { 
										deleteCommentRow(row.count, row.tableId); 
									}
								}
			buttonEl.setAttribute('name', deleteCommentName);
			buttonEl.setAttribute('value', deleteCommentValue);
			buttonEl.className = 'button';
			cell.appendChild(buttonEl);
			rows_nb_per_group[table_id] = rows_nb_per_group[table_id] + 1;
			count++;
			document.getElementById('product_count').value = count;
		}	

	function addRow(id, quantity, product_template_id, product_name, cost_price, 
					list_price, discount_price, pricing_formula, pricing_formula_name, pricing_factor, 
					tax_class, tax_class_name, mft_part_num, table_id, bundle_stage, 
					bundle_name,bundle_shipping,product_description, type_id,discount_amount,
					discount_select, deal_calc, product_status)
	{
		    
			if(!table_exists(table_id)){
				table_id = addTable(table_id, bundle_stage, bundle_name, bundle_shipping);
			}

			the_quantity = (quantity == '') ? 1 : quantity;	
			var unit_price;
			if (discount_price == '') unit_price = '0' + dec_sep + '00';
			else unit_price = discount_price;	
			if (discount_select == '') discount_select = false;
			if (deal_calc == '') deal_calc=0;		
			if(product_status == '') product_status = default_product_status;
			var form = document.getElementById('EditView')
			var table = document.getElementById(table_id);
			var row = table.insertRow(table.rows.length );

			var rowName = 'item_row_' + count;
		    table_array[table_id][rows_nb_per_group[table_id]] = parseFloat(count);
			rows_nb_per_group[table_id] = rows_nb_per_group[table_id] + 1;
			
			// add quicksearch stuff to the array
			var sqs_id = form.id + '_product_name[' + count + ']';
			sqs_objects[sqs_id] = {
				"id" : sqs_id,
				"form" : form.id,
				"method" : "query",
				"modules" : ["ProductTemplates"],
				"group" : "or",
				"field_list" : ["name","id","type_id","mft_part_num","cost_price","list_price",
								"discount_price","tax_class", "pricing_factor", "description", 
								"cost_usdollar", "list_usdollar", "discount_usdollar", "tax_class_name"],
				"populate_list" : ["name_" + count,"product_template_id_" + count],
				"conditions" : [{"name":"name","op":"like_custom","end":"%","value":""}],
				"order" : "name",
				"limit" : "30",
				"no_match_text" : sqs_no_match_text,
				"post_onblur_function" : "set_after_sqs"};

			row.setAttribute('valign', 'top');

			row.id = rowName;
			row.tableId = table.id;
			row.count = count;
			var cell = row.insertCell(row.cells.length);
			cell.nowrap = 'nowrap';
			var buttonEl = document.createElement('input');
			buttonEl.setAttribute('type', 'button');
			buttonEl.count = count;
			buttonEl.onclick= function(){grab_table_row('item_row_' + this.count);}
			buttonEl.setAttribute('name', '||');
			buttonEl.setAttribute('value', '||');
			buttonEl.className = 'button';
			cell.appendChild(buttonEl);
			
			var cell = row.insertCell(row.cells.length);
			cell.nowrap = 'nowrap';
			var textEl = document.createElement('input');
			textEl.setAttribute('type', 'hidden')
			textEl.setAttribute('name',  "product_id[" + count + "]" );
			textEl.setAttribute('id', "product_id_" + count );
			textEl.value = id;
			item_list_MSI["product_id_[" + count + "]"] = textEl;
			form.appendChild(textEl);
			
			var textEl = document.createElement('input');
			textEl.setAttribute('type', 'hidden')
			textEl.setAttribute('name',  "delete[" + count + "]" );
			textEl.setAttribute('id', "delete_" + count  );
			textEl.value = 1;
			item_list_MSI["delete[" + count + "]"] = textEl;
			form.appendChild(textEl);
			
			var textEl = document.createElement('input');
			textEl.setAttribute('type', 'hidden')
			textEl.setAttribute('name',  "type_id[" + count + "]" );
			textEl.setAttribute('id', "type_id_" + count );
			textEl.value = type_id;
			item_list_MSI["type_id[" + count + "]"] = textEl;
			form.appendChild(textEl);
			
			var textEl = document.createElement('input');
			textEl.setAttribute('type', 'hidden')
			textEl.setAttribute('name',  "product_template_id[" + count + "]" );
			textEl.setAttribute('id', "product_template_id_" + count );
			textEl.setAttribute('value', product_template_id);
			item_list_MSI["product_template_id[" + count + "]"] = textEl;
			form.appendChild(textEl);
			
			var textEl = document.createElement('input');
			textEl.setAttribute('type', 'hidden')
			textEl.setAttribute('name',  "status[" + count + "]" );
			textEl.setAttribute('id', "status[" + count + "]" );
			textEl.setAttribute('value', product_status);
			item_list_MSI["status[" + count + "]"] = textEl;
			form.appendChild(textEl);
			
			
			var textEl = document.createElement('input');
			textEl.setAttribute('type', 'hidden')
			textEl.setAttribute('name',  "tax_class[" + count + "]" );
			textEl.setAttribute('id', "tax_class_" + count );
			textEl.setAttribute('value', tax_class);
			item_list_MSI["tax_class[" + count + "]"] = textEl;
			cell.appendChild(textEl);
			
			var textEl = document.createElement('input');
			textEl.setAttribute('type', 'hidden')
			textEl.setAttribute('name',  "parent_group[" + count + "]" );
			textEl.setAttribute('id', "parent_group_" + count );
			textEl.setAttribute('value', table_id);
			item_list_MSI["parent_group_"+ count] = textEl;
			cell.appendChild(textEl);
			
			var textEl = document.createElement('input');
			textEl.setAttribute('type', 'hidden')
			textEl.setAttribute('name',  "parent_group_index[" + count + "]" );
			textEl.setAttribute('id', "parent_group_index" + count );
			textEl.setAttribute('value', row.rowIndex);
			item_list_MSI["parent_group_index_"+ count] = textEl;
			cell.appendChild(textEl);
			
			var textEl = document.createElement('input');
			textEl.setAttribute('type', 'hidden');
			textEl.setAttribute('name',  "parent_group_position[" + count + "]" );
			textEl.setAttribute('id', "parent_group_position" + count );
			textEl.setAttribute('value', count);
			item_list_MSI["parent_group_position_"+ count] = textEl;
			cell.appendChild(textEl);			
			
			//quantity
			cell.width=55;
			var textEl = document.createElement('input');
			var quantName = 'quantity_' + count;
			textEl.setAttribute('type', 'text');
			textEl.size = 4;
			textEl.tabIndex = 6;
			textEl.setAttribute('name',  "quantity[" + count + "]" );
			textEl.setAttribute('id', "quantity_" + count );
			textEl.setAttribute('value', the_quantity);
			item_list_MSI["quantity[" + count + "]"] = textEl;
			textEl.onchange= function(){
								if (isInteger(lookup_item(quantName, document).value)){ 
									calculate(document); } else { alert (invalidAmount); 
									
									alert (lookup_item(quantName, document).value);
									
									lookup_item(quantName, document).select();
								}
							   }
			cell.appendChild(textEl);
			
			//product name
			var cell1 = row.insertCell(row.cells.length);
			cell1.width= 200;
			cell1.noWrap = true;
			var itemName = 'name_' + count;
			var textEl = document.createElement('input');
			textEl.setAttribute('type', 'text')
			textEl.size = 30;
			textEl.tabIndex = 6;
			textEl.count = count;
			textEl.value = product_name;
			textEl.alt = function(){lookup_item(itemName, document);}
			textEl.className += "sqsEnabled sqsNoAutofill";
			textEl.setAttribute('name',  "product_name[" + count + "]" );
			textEl.setAttribute('id', itemName );
			textEl.onchange = function(){toEdit(document, this.count);}
			item_list_MSI[itemName] = textEl;
			cell1.appendChild(textEl);
			cell1.appendChild(document.createElement('div'));
			var itemName = 'description_' + count;
			var textEl = document.createElement('textarea');
			textEl.setAttribute('rows', 3);
			textEl.setAttribute('cols',  30	);
			textEl.count = count;
			product_description = product_description.replace(/&#039;/g, '\'');
			textEl.value = product_description.replace(/<br>/g, '\n');
			textEl.alt = function(){lookup_item(itemName, document);}
			textEl.setAttribute('name',  "product_description[" + count + "]" );
			textEl.setAttribute('id', itemName );
			item_list_MSI[itemName] = textEl;
			cell1.appendChild(textEl);
			var cellb = row.insertCell(row.cells.length);
			cellb.width= 55;
			cellb.noWrap = true;
			var spanEl = document.createElement('span');
			spanEl.className = 'id-ff';
			cellb.appendChild(spanEl);
			var buttonEl = document.createElement('button');
			var itemName = 'product_name_select_' + count;
			buttonEl.setAttribute('type', 'button');
			buttonEl.title = selectButtonTitle;
			buttonEl.accessKey = selectButtonKey;
			buttonEl.value = selectButtonValue;
			buttonEl.innerHTML = '<img src="index.php?entryPoint=getImage&imageName=id-ff-select.png&themeName='+SUGAR.themes.theme_name+'">';
			buttonEl.textElement = 'name_' + count;
			buttonEl.count = count;
			buttonEl.tabIndex = 6;
			buttonEl.setAttribute('name',  "btn_product_name[" + count + "]" );
			buttonEl.setAttribute('id', itemName );
			buttonEl.onclick = function(){openPopup( lookup_item(this.textElement, document).value,this.count);}
			buttonEl.className = 'button';
			spanEl.appendChild(buttonEl);
			
			//mft_part
			var cell2 = row.insertCell(row.cells.length);
			cell2.width = 75;
			var textEl = document.createElement('input');
			var itemName = 'mft_part_num_' + count;
			textEl.setAttribute('type', 'text')
			textEl.size = 10;
			textEl.setAttribute('name',  "mft_part_num[" + count + "]" );
			textEl.tabIndex = 6;
			textEl.setAttribute('id', itemName );
			textEl.setAttribute('value', mft_part_num);
			item_list_MSI[itemName] = textEl;
			cell2.appendChild(textEl);
			
			var textEl = document.createElement('input');
			var itemName = 'pricing_factor_' + count;
			textEl.setAttribute('type', 'hidden')
			textEl.size = 4;
			textEl.setAttribute('name',  "pricing_factor[" + count + "]" );
			textEl.setAttribute('id', itemName );
			textEl.setAttribute('value', pricing_factor);
			item_list_MSI[itemName] = textEl;
			cell2.appendChild(textEl);
			
			var divselect = document.createElement('div');
			divselect.setAttribute('id', 'taxselect' + count);
			divselect.style.display = 'none';
			item_list_MSI['taxselect' + count] = divselect;
			var cell3 = row.insertCell(row.cells.length);
			cell3.width = 100;
			var selectEl = document.createElement('select');

			selectEl.count = count;
			selectEl.onchange = function(){taxSelectChanged(document,this.count);} 
			var itemName = 'tax_class_select_name_' + count;
			selectEl.setAttribute('name',  "tax_class_select_name[" + count + "]" );
			selectEl.setAttribute('id', itemName );
			selectEl.tabIndex = 6;
			for(i = 0; i < tax_rate_keys.length; i++){
				var optionEl = document.createElement('option');
				optionEl.text = tax_rate_keys[i];
				optionEl.value = tax_rates[optionEl.text];
				try{
					selectEl.add(optionEl, null);
				}catch(ex){
					selectEl.add(optionEl);
				}	
			}
			
			divselect.appendChild(selectEl);
			cell3.appendChild(divselect);
			item_list_MSI[itemName] = selectEl;
			
			
			var divnoselect = document.createElement('div');
			divnoselect.setAttribute('id', 'taxinput' + count);
			divnoselect.style.display = 'none';
			item_list_MSI['taxinput' + count] = divselect;
			var textEl = document.createElement('input');
			var itemName = 'tax_class_name_' + count;
			textEl.setAttribute('type', 'input');
			textEl.size = 8;
			textEl.tabIndex = 6;
			textEl.setAttribute('name', 'tax_class_name[' + count + ']');
			textEl.setAttribute('id', itemName);
			textEl.setAttribute('value', tax_class_name);
			item_list_MSI[itemName] = textEl;
			divnoselect.appendChild(textEl);
			cell3.appendChild(divnoselect);
			
			var cell4 = row.insertCell(row.cells.length);
			cell4.width = 75;
			var textEl = document.createElement('input');
			var itemName = 'cost_price_' + count;
			textEl.setAttribute('type', 'text')
			textEl.size = 8;
			textEl.style.textAlign = 'right';
			textEl.tabIndex = 6;
			textEl.setAttribute('name',  "cost_price[" + count + "]" );
			textEl.setAttribute('id', itemName );
			textEl.setAttribute('value', cost_price);
			textEl.onchange = function() { if(!isAmount(toDecimal(unformatNumber(this.value, num_grp_sep, dec_sep), precision))) { alert (invalidAmount); this.select(); } };
			item_list_MSI[itemName] = textEl;
			cell4.appendChild(textEl);
			
			var cell5 = row.insertCell(row.cells.length);
			cell5.width = 75;
			var textEl = document.createElement('input');
			var itemName = 'list_price_' + count;
			textEl.setAttribute('type', 'text')
			textEl.size = 8;
			textEl.style.textAlign = 'right';
			textEl.tabIndex = 6;
			textEl.setAttribute('name',  "list_price[" + count + "]" );
			textEl.setAttribute('id', itemName );

			textEl.setAttribute('value', list_price);
			textEl.onchange = function() { if(!isAmount(toDecimal(unformatNumber(this.value, num_grp_sep, dec_sep),precision))) { alert (invalidAmount); this.select(); } } ;
			item_list_MSI[itemName] = textEl;
			cell5.appendChild(textEl);
			
			var cell6 = row.insertCell(row.cells.length);
			cell6.width = 80;
			var textEl = document.createElement('input');
			var itemName = 'discount_price_' + count;
			textEl.setAttribute('type', 'text')
			textEl.size = 8;
			textEl.style.textAlign = 'right';
			textEl.tabIndex = 6;
			textEl.setAttribute('name',  "discount_price[" + count + "]" );
			textEl.setAttribute('id', itemName );
			textEl.setAttribute('value', unit_price);
			textEl.onchange = function() { if(isAmount(toDecimal(unformatNumber(this.value, num_grp_sep, dec_sep),precision))) { calculate(document); } else { alert (invalidAmount); this.select(); } } ;
			item_list_MSI[itemName] = textEl;
			cell6.appendChild(textEl);
			var params = textEl.value;	
			var params1 = '"' + count + '", "' + id + '"';
			
			var cell7 = row.insertCell(row.cells.length);
            cell7.width = 60;
			var divselect = document.createElement('div');
            divselect.setAttribute('id', 'discount_amount_div' + count);
            item_list_MSI['discount_amount_div' + count] = divselect;
			cell7.appendChild(divselect);
            var textEl = document.createElement('input');
            var itemName = 'discount_amount_' + count;
            textEl.setAttribute('type', 'text')
            textEl.size = 4;
            textEl.style.textAlign = 'right';
            textEl.tabIndex = 6;
            textEl.setAttribute('name',  "discount_amount[" + count + "]" );
            textEl.setAttribute('id', itemName );
            textEl.setAttribute('value', discount_amount);
            textEl.count = count;
            textEl.onchange = function() { if(isAmount(toDecimal(unformatNumber(this.value, num_grp_sep, dec_sep),precision))) { discount_calculated(document, this.count); } else { alert (invalidAmount); this.select(); } } ;
            item_list_MSI[itemName] = textEl;
            divselect.appendChild(textEl);

            var params1 = '"' + count + '", "' + id + '"';

			
			
			
		/*	var divselect = document.createElement('div');
			divselect.setAttribute('width', '100');
            divselect.setAttribute('id', 'discount_amount_select' + count);
            item_list_MSI['discount_amount_select' + count] = divselect;*/
            var cell8 = row.insertCell(row.cells.length);
            cell8.width = 20;			

			var newtext = document.createTextNode("in\u00A0%");
            cell8.appendChild(newtext);
			
			var cell9 = row.insertCell(row.cells.length);
            cell9.width = 50;
			var ele2 = document.createElement('td');
            var textEl = document.createElement('input');
            
            var itemName = 'checkbox_select_' + count;
            textEl.setAttribute('name',  "checkbox_select[" + count + "]" );
            textEl.setAttribute('id', itemName );
			textEl.setAttribute('type', 'checkbox');
            textEl.setAttribute('class', 'checkbox');
			textEl.setAttribute('value', '1');

			
			textEl.tabIndex = 6;
			textEl.count = count;
			textEl.onclick = function(){ discount_calculated(document, this.count); }
           
			cell9.appendChild(ele2);
			ele2.appendChild(textEl);
			
			// it has to happen after ele2.appendChild(textEl) otherwise the checkox won't be checked in IE7
			if(discount_select==true){
                textEl.setAttribute('checked', true);
            }
            item_list_MSI[itemName] = textEl;  
			
			var divnoselect = document.createElement('div');
            divnoselect.setAttribute('id', 'deal_calc' + count);
            divnoselect.style.display = 'none';
            item_list_MSI['deal_calc' + count] = divselect;
			
            var inputselect = document.createElement('input');
            inputselect.setAttribute('name',  "discount_select[" + count + "]" );
            var itemName = 'discount_select_' + count;
            inputselect.setAttribute('id', itemName );
            inputselect.setAttribute('value', discount_select); 
            
            divnoselect.appendChild(inputselect);
            item_list_MSI[itemName] = inputselect;			
			
            var textEl = document.createElement('input');
            var itemName = 'deal_calc_' + count;
            textEl.setAttribute('type', 'input');
            textEl.size = 8;
            textEl.tabIndex = 2;
            textEl.setAttribute('name', 'deal_calc[' + count + ']');
            textEl.setAttribute('id', itemName);
            textEl.setAttribute('value', deal_calc);
            item_list_MSI[itemName] = textEl;
            divnoselect.appendChild(textEl);
            cell9.appendChild(divnoselect);
			
			
			var cell10 = row.insertCell(row.cells.length);
			var buttonEl = document.createElement('input');
			buttonEl.setAttribute('type', 'button');
			buttonEl.setAttribute('id', 'delete_row' + count);
			buttonEl.tableId = table_id;
			buttonEl.tabIndex = 6;
			buttonEl.count = count;
			buttonEl.onclick= function(){
									if (confirm(deleteButtonConfirm)) { 
										deleteRow(this.count, this.tableId); 
										calculate(document); 
									}
								}
			buttonEl.setAttribute('name', deleteButtonName);
			buttonEl.setAttribute('value', deleteButtonValue);
			buttonEl.className = 'button';
			cell10.appendChild(buttonEl);
			
			
			
			
			toEdit(document, this.count);
			toReadOnly(document, count);
			registerSingleSmartInputListener(document.getElementById('name_'+count));
			
			count++;
			document.getElementById('product_count').value = count;	
			//do the initial calculate to run when page loads.	
			calculate(document); 	
		}

 
function hasAttribute(element, attr) {
    if (element.hasAttribute) return element.hasAttribute(attr);
    return (typeof(element.getAttribute(attr)) == typeof(''));
}



function deleteRow(id, table_id) {
	for (var i in table_array[table_id]){
		if(table_array[table_id][i]==id){
			table_array[table_id].splice(i,1);
		}
	}
	ungrab_table_row();
	var table = document.getElementById(table_id);
	var rows = table.rows;
	var looking_for = 'delete_row' + id;
	for(i = 1 ; i < rows.length; i++) {
		cells = rows[i].cells;
		for(var j = 9 ; j < rows[i].cells.length; j++) {
			cell = rows[i].cells[j];
			children = cell.childNodes;
			for(var k = 0 ; k < children.length; k++) {
				var child = children[k];
				if(child.nodeType == 1 && hasAttribute(child, 'id')) {
					if(child.getAttribute('id') == looking_for) {
						table.deleteRow(i);
						document.getElementById('delete_' + id).value = document.getElementById('product_id_' + id).value;
						return;
					}
				}
			}
		}
	}
}
		
function deleteCommentRow(id, table_id)
{
	ungrab_table_row();
	var table = document.getElementById(table_id);
	var rows = table.rows;
	var looking_for = 'item_row_' + id;
	for(var i = 0 ; i < rows.length; i++){
		if (rows[i].id == looking_for) {
			table.deleteRow(i);
			document.getElementById('comment_delete_' + id).value = document.getElementById('comment_id_' + id).value;
			return;
		}
	}
}
function toggleDisplay(id){
		
			if(this.document.getElementById(id).style.display=='none'){
				this.document.getElementById(id).style.display='inline'
				if(this.document.getElementById(id+"link") != undefined){
					this.document.getElementById(id+"link").style.display='none';
				}
						
			}else{
				this.document.getElementById(id).style.display='none'
				if(this.document.getElementById(id+"link") != undefined){
					this.document.getElementById(id+"link").style.display='inline';
				}
			}
		} 
function calculate(doc) {
	var gt = Array();
	warned = false;
	gt['tax'] = 0;
	gt['subtotal'] = 0;
	gt['total'] = 0;
	gt['shipping'] = 0;
	gt['discount'] = 0;
	gt['new_sub'] = 0;
	for(var table_count = 0; table_count < table_list.length; table_count++){
		cur_table_id = table_list[table_count];
		var table = doc.getElementById(cur_table_id);
		if(table != null && typeof(table) != 'undefined'){
			var bundle_stage = doc.getElementById("bundle_stage_" + cur_table_id).value;
			var is_custom_group_stage = isCustomGroupStage(bundle_stage);
			if(!is_custom_group_stage) {
				var retval = calculate_table(doc, cur_table_id);
				gt['tax'] += retval['tax'];
				gt['subtotal'] += retval['subtotal'];
				gt['discount'] += retval['discount'];
				gt['total'] += retval['total'];
				gt['new_sub'] += retval['new_sub'];
				if(retval['shipping'] != '') gt['shipping'] += parseFloat(retval['shipping']);
			}	
		}
	}

	lookup_item('grand_total', document).innerHTML = formatNumber(toDecimal(gt['total'], precision), num_grp_sep, dec_sep, precision, precision);
	lookup_item('grand_ship', document).innerHTML = formatNumber(toDecimal(gt['shipping'], precision), num_grp_sep, dec_sep, precision, precision);
	lookup_item('grand_tax', document).innerHTML =  formatNumber(toDecimal(gt['tax'], precision), num_grp_sep, dec_sep, precision, precision);
	lookup_item('grand_new_sub', document).innerHTML =  formatNumber(toDecimal(gt['new_sub'], precision), num_grp_sep, dec_sep, precision, precision);
	lookup_item('grand_discount', document).innerHTML =  formatNumber(toDecimal(gt['discount'], precision), num_grp_sep, dec_sep, precision, precision);
	lookup_item('grand_sub', document).innerHTML =  formatNumber(toDecimal(gt['subtotal'], precision), num_grp_sep, dec_sep, precision, precision);

}


function calculate_table(doc, table_id){
	var retval = Array();
	retval['subtotal'] = calculate_subtotal(doc, table_id);
	retval['discount'] = calculate_discount(doc, table_id);
	retval['new_sub'] = calculate_new_sub(doc, table_id);
	retval['tax'] = calculate_tax(doc, table_id);
	retval['total'] = calculate_total(doc, table_id);
	retval['shipping'] = unformatNumber(lookup_item('shipping_' + table_id).value, num_grp_sep, dec_sep);
	return retval;
}

/*
	formula is discount_price * quantity * 1.0;
*/


function walk_the_kids(doc, children, variables, variable_values){
	for(k = 0; k < children.length; k++){
				child = children[k];
				if(child.nodeType == 1 && 
				   hasAttribute(child, 'id') && 
				   child.getAttribute('id') != child.getAttribute('name') && 
				   child.tagName != 'LI' &&
				   (child.tagName != 'DIV' || child.style.display != 'NONE')) {
					
					var id = child.getAttribute('id');
					
					for(n = 0; n < variables.length; n++){
						var reg =  new RegExp('^' + variables[n] + '[0-9]+$');
						if(reg.test(id)){
							if(child.tagName == 'SELECT'){
								var select = lookup_item(id, document);
								variable_values[variables[n]] = select.options[select.selectedIndex].value;
							}else{
								variable_values[variables[n]] = lookup_item(id, document).value;
							}
						}
				
						if(child.childNodes.length > 0 && child.tagName != 'OPTION' && child.tagName != 'TEXTAREA'){
							variable_values = walk_the_kids(doc, child.childNodes, variables, variable_values);
						}
								
					}
				}
	}
	return variable_values;
}

var warned = false;
function calculate_formula(doc, formula, table_id){
	var total = 0.00;
	var formula_type = '';
	if (formula != 'discount_amount' && formula != 'tax') {	
		var variables = formula.match(/(_var_[a-zA-Z\_]+)+/g);
		var variable_values = new Array();
		formula = formula.replace(/(_var_)/g, '');
		for(q =0; q < variables.length; q++){
			variables[q] = trim(variables[q]).replace(/(_var_)/g, '');
		}
	}else
	{
		formula_type = formula;
	}

	var table = doc.getElementById(table_id);
	var rows = table.rows;
	for(var i = 0 ; i < rows.length; i++){	
		if (formula_type == 'discount_amount') {
			formula = "unformatNumber('_var_discount_amount_', num_grp_sep, dec_sep) / 100 " 
				  + "* unformatNumber('_var_discount_price_', num_grp_sep, dec_sep) "
				  + "* unformatNumber('_var_quantity_', num_grp_sep, dec_sep) * 1.0";
			if (i != 0) {
				
            	var chckd = null;
				var ckId = 'checkbox_select_' + table_array[table_id][i];
                //retrieve the value of percentage discount check box
                if(
                		typeof(rows[i].cells[11])!='undefined'
                        && typeof(rows[i].cells[11].childNodes[0]) !='undefined'
                        && typeof(rows[i].cells[11].childNodes[0].childNodes[0]) !='undefined'
                ){//check box was found, retrieve value through dom tree.  We try this way first as it is more
                  //reliable when rows have been removed than relying on count
                        chckd = rows[i].cells[11].childNodes[0].childNodes[0].checked;
                }else{
                //check box was not found, retrieve value directly
                        chckd = doc.getElementById(ckId) && doc.getElementById(ckId).checked;
                }

				if (chckd) {
					formula = "unformatNumber('_var_discount_amount_', num_grp_sep, dec_sep) / 100 " +
							"* unformatNumber('_var_discount_price_', num_grp_sep, dec_sep) " +
							"* unformatNumber('_var_quantity_', num_grp_sep, dec_sep) * 1.0";
				}
				else {
					formula = "unformatNumber('_var_discount_amount_', num_grp_sep, dec_sep) " +
					"* unformatNumber('_var_quantity_', num_grp_sep, dec_sep) * 1.0";
				}
			}
			var variables = formula.match(/(_var_[a-zA-Z\_]+)+/g);
			var variable_values = new Array();
			formula = formula.replace(/(_var_)/g, '');
		}
		if(formula_type == 'tax')
		{
			var taxrate_value = "taxrate_value";
			var taxrate = 0.00;
			if(doc.EditView.taxrate_id.options.selectedIndex > -1){
				taxrate=get_taxrate(doc.EditView.taxrate_id.options[doc.EditView.taxrate_id.options.selectedIndex].value);
			}
			var taxable = SUGAR.language.get('app_list_strings','tax_class_dom');
			taxable = taxable['Taxable'];
			var formula_discount = "unformatNumber('_var_discount_amount_', num_grp_sep, dec_sep) / 100 * unformatNumber('_var_discount_price_', num_grp_sep, dec_sep) * unformatNumber('_var_quantity_', num_grp_sep, dec_sep) * 1.0";
			if (i != 0) {    
            	var chckd = null;
				var ckId = 'checkbox_select_' + table_array[table_id][i];
                //retrieve the value of percentage discount check box
                if(
                		typeof(rows[i].cells[11])!='undefined'
                        && typeof(rows[i].cells[11].childNodes[0]) !='undefined'
                        && typeof(rows[i].cells[11].childNodes[0].childNodes[0]) !='undefined'
                ){//check box was found, retrieve value through dom tree.  We try this way first as it is more
                  //reliable when rows have been removed than relying on count
                        chckd = rows[i].cells[11].childNodes[0].childNodes[0].checked;
                }else{
                //check box was not found, retrieve value directly
                        chckd = doc.getElementById(ckId) && doc.getElementById(ckId).checked;
                }

				if (chckd) {
					formula_discount = "unformatNumber('_var_discount_amount_', num_grp_sep, dec_sep) / 100 * unformatNumber('_var_discount_price_', num_grp_sep, dec_sep) * unformatNumber('_var_quantity_', num_grp_sep, dec_sep) * 1.0";
				}
				else {
					formula_discount = "unformatNumber('_var_discount_amount_', num_grp_sep, dec_sep) * unformatNumber('_var_quantity_', num_grp_sep, dec_sep) * 1.0";
				} 
			}
			formula = "(unformatNumber('_var_discount_price_', num_grp_sep, dec_sep) * unformatNumber('_var_quantity_', num_grp_sep, dec_sep) * 1.0 - "+formula_discount+") * " + taxrate + " * 1.0 * (('_var_tax_class_' == " + "'" + taxable + "') || ('_var_tax_class_' == " + "'Taxable'))";
			var variables = formula.match(/(_var_[a-zA-Z\_]+)+/g);
			var variable_values = new Array();
			formula = formula.replace(/(_var_)/g, '');

		}
		for(q =0; q < variables.length; q++){
			variables[q] = trim(variables[q]).replace(/(_var_)/g, '');
		}
		cells = rows[i].cells;
		for(q =0; q < variables.length; q++){
			variable_values[variables[q]] = 0;
		}
		for(j = 0; j < cells.length; j++){
			cell = rows[i].cells[j];
			children = cell.childNodes;
			if ( typeof ( cell.childNodes) == 'undefined' || cell.childNodes == null)
			{
				continue;
			}
			variable_values = walk_the_kids(doc, children, variables, variable_values);
		}

		var newformula = formula;

		for(z =0; z < variables.length; z++){
			var reg =  variables[z] ;
			newformula = newformula.replace(reg, variable_values[variables[z]]);
		}
		try{
			total = total + eval(newformula);
		}catch(exception){
			if(!warned){
				alert(check_data);
				warned = true;
			}
			return 0;
		}
	}

	return total;
}

function calculate_subtotal(doc, table_id){
	var subtotal = 0.00;
	subtotal = calculate_formula(doc, "unformatNumber('_var_discount_price_', num_grp_sep, dec_sep) * unformatNumber('_var_quantity_', num_grp_sep, dec_sep) * 1.0", table_id);
	lookup_item('subtotal_' + table_id, doc).value = formatNumber(toDecimal(subtotal, precision), num_grp_sep, dec_sep, precision, precision);
	return subtotal;
}

function calculate_discount(doc, table_id){
    var discount = 0.00;
    discount = calculate_formula(doc, "discount_amount", table_id);
    lookup_item('deal_tot_' + table_id, doc).value = formatNumber(toDecimal(discount, precision), num_grp_sep, dec_sep, precision, precision);
    return discount;
}

function calculate_new_sub(doc, table_id){
	var new_sub = 0.00;
    new_sub = unformatNumber(lookup_item('subtotal_'+ table_id, doc).value, num_grp_sep, dec_sep) - unformatNumber(lookup_item('deal_tot_'+table_id, doc).value, num_grp_sep, dec_sep);
    lookup_item('new_sub_' + table_id, doc).value = formatNumber(toDecimal(new_sub, precision), num_grp_sep, dec_sep, precision, precision);
    return new_sub;
}

function calculate_tax(doc, table_id){
    var tax = 0.00;
    tax = calculate_formula(doc, 'tax', table_id);
	lookup_item('tax_' + table_id, doc).value = formatNumber(toDecimal(tax, precision), num_grp_sep, dec_sep, precision, precision);
	return tax;
}

function calculate_total(doc, table_id){
	var total = 0.00;
	var discount_price;
	var quantity;
	var delete_me;
	
	total += unformatNumber(lookup_item('subtotal_'+ table_id, doc).value, num_grp_sep, dec_sep) + 
			 unformatNumber(lookup_item('tax_' + table_id, doc).value, num_grp_sep, dec_sep) + 
			 unformatNumber(lookup_item('shipping_'+table_id, doc).value, num_grp_sep, dec_sep) -
			 unformatNumber(lookup_item('deal_tot_'+table_id, doc).value, num_grp_sep, dec_sep);
			 
	lookup_item('total_' + table_id, doc).value = formatNumber(toDecimal(total, precision), num_grp_sep, dec_sep, precision, precision);
	return total;
}

function ConvertItems(id) {
	var items = new Array();
	for (y=0;y<count;y++){
		var discount_price = lookup_item("discount_price_" + y, document);
		var list_price = lookup_item("list_price_" + y, document);
		var cost_price = lookup_item("cost_price_" + y, document);

		if(discount_price != null && typeof(discount_price ) != 'undefined') {
			discount_price.value = unformatNumber(discount_price.value, num_grp_sep, dec_sep);
			list_price.value = unformatNumber(list_price.value, num_grp_sep, dec_sep);
			cost_price.value = unformatNumber(cost_price.value, num_grp_sep, dec_sep);
			
			items[items.length] = list_price;
			items[items.length] = cost_price;
			items[items.length] = discount_price;
		}
	}
	
	ConvertRate(id, items);

	for (y=0;y<count;y++){
		var discount_price = lookup_item("discount_price_" + y, document);
		var list_price = lookup_item("list_price_" + y, document);
		var cost_price = lookup_item("cost_price_" + y, document);

		if(discount_price != null && typeof(discount_price ) != 'undefined') {
			discount_price.value = formatNumber(discount_price.value, num_grp_sep, dec_sep, precision, precision);
			list_price.value = formatNumber(list_price.value, num_grp_sep, dec_sep, precision, precision);
			cost_price.value = formatNumber(cost_price.value, num_grp_sep, dec_sep, precision, precision);
		}		
	}
		
	calculate(document);
}
function isAmount(amount) {

	if (amount < 0) {
		the_amount = amount * -1;
	}
	else {
		the_amount = amount;
	}
	return isFloat(the_amount);
}

function isCustomGroupStage(value) {
	var quote_dom = SUGAR.language.get('app_list_strings', 'in_total_group_stages');
	for(var v in quote_dom) {
		if(value == v) {
			return false;
		}
	}
	return true;
}
