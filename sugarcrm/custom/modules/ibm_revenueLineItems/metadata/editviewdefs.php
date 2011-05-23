<?php
$viewdefs['ibm_revenueLineItems'] = 
array (
  'EditView' => 
  array (
    'templateMeta' => 
    array (
      'maxColumns' => '2',
      'widths' => 
      array (
        0 => 
        array (
          'label' => '10',
          'field' => '30',
        ),
        1 => 
        array (
          'label' => '10',
          'field' => '30',
        ),
      ),
      'useTabs' => false,
    ),
    'panels' => 
    array (
      'default' => 
      array (
        0 => 
        array (
          0 => array(
			'name' => 'search_type',
			'label' => 'LBL_SEARCH_TYPE',
          	'customCode' => '
          		<div id="search_type_div">
          		<input type="radio" name="search_type_radio" id="search_type_radio" value="product" {if $fields.search_type.value == "product" || $fields.search_type.value == ""}checked="checked"{/if} onclick="changeSearchType(\'product\');" />Product Offering &nbsp;&nbsp;&nbsp;
          		<input type="radio" name="search_type_radio" id="search_type_radio" value="sei" {if $fields.search_type.value == "sei"}checked="checked"{/if} onclick="changeSearchType(\'sei\');" />SEI Solution
          		<input type="hidden" name="search_type" id="search_type" value="{$fields.search_type.value}" />
          		</div> 
          		',
			),
          1 => 
          array (
          ),
        ),
      
        1 => 
        array (
          0 => array(
			'name' => 'name',
			'label' => 'LBL_FIND_PRODUCT',
			'customLabel' => '{if $fields.search_type.value == "sei"}
								Search for SEI Solutions:
							{else}
								Search for Product Offerings:
							{/if}',
			'type' => 'RevenueLineItems',
			),
          1 => 
          array (
            'name' => 'assigned_user_name',
            'label' => 'LBL_ASSIGNED_TO_NAME',
          ),
        ),
        2 => 
        array (
          0 => array(
			'name' => 'name',
			'customLabel' => '',
			'customCode' => '<div id="name_footer">
				{if $fields.search_type.value == "sei"}
					Or, find SEI Solutions by level using the fields below.
				{else}
					Or, find Product Offerings by level using the fields below.
				{/if}
				</div>',
			),
          1 => 
          array (
            'name' => 'probability',
            'label' => 'LBL_PROBABILITY',
          ),
        ),
        3 => 
        array (
          0 => 
          array (
            'name' => 'offering_type',
            'label' => 'LBL_OFFERING_TYPE',
			'type' => 'RevenueLineItems',

          ),
          1 => 
          array (
            'name' => 'bill_date',
            'label' => 'LBL_BILL_DATE',
          ),
        ),
        4 => 
        array (
          0 => 
          array (
            'name' => 'sub_brand_c',
            'label' => 'LBL_SUB_BRAND',
			'type' => 'RevenueLineItems',
          ),
          1 => 
          array (
            'name' => 'quantity',
            'label' => 'LBL_QUANTITY',
          ),
        ),
        5 => 
        array (
          0 => 
          array (
            'name' => 'brand_code',
            'label' => 'LBL_BRAND_CODE',
			'type' => 'RevenueLineItems',
          ),
          1 =>
          array (
            'name' => 'revenue_amount',
            'label' => 'LBL_REVENUE_AMOUNT',
            'customCode' => '
{counter name="panelFieldCount"}
{if strlen($fields.revenue_amount.value) <= 0}
{assign var="value" value=$fields.revenue_amount.default_value }
{else}
{assign var="value" value=$fields.revenue_amount.value }
{/if}
<span id="revenue_amount">
<input type="text" name="{$fields.revenue_amount.name}" id="{$fields.revenue_amount.name}" size="30"  value="{sugar_number_format var=$value}" title="" tabindex="108" >
</span>
&nbsp;{$MOD.LBL_IN_CURRENCY}:&nbsp;<span id="ra_currency_id">{$fields.ra_currency_id.value}</span>
',
            ),
        ),
        6 => 
        array (
          0 => 
          array (
            'name' => 'product_information',
            'label' => 'LBL_PRODUCT_INFORMATION',
			'type' => 'RevenueLineItems',
          ),
          1 => '' 
        ),
        7 => 
        array (
          0 => array(
			'name' => 'machine_type',
          	'label' => 'LBL_MACHINE_TYPE',
          	'type' => 'RevenueLineItems',
			),
          1 =>           array (
            'name' => 'revenue_type',
            'studio' => 'visible',
            'label' => 'LBL_REVENUE_TYPE',
			'customCode' => '
{counter name="panelFieldCount"}

{if empty($fields.revenue_type.value)}
{assign var="value" value=$fields.revenue_type.default_value }
{else}
{assign var="value" value=$fields.revenue_type.value }
{/if}
{capture name=idname assign=idname}{$fields.revenue_type.name}{/capture}
{if isset($fields.revenue_type.value) && $fields.revenue_type.value != ""}
{html_radios id="$idname" name="$idname" title="" options=$fields.revenue_type.options selected=$fields.revenue_type.value separator="<br>"}
{else}
{html_radios id="$idname" name="$idname" title="" options=$fields.revenue_type.options selected=$fields.revenue_type.default separator="<br>"}
{/if}
{literal}
<script type="text/javascript">
var ibm_revenue_type_helper_function = function(){
    var nodes = document.getElementsByName("revenue_type");
    for(x in nodes){
        if(nodes[x].checked && nodes[x].value != "transactional"){
            document.getElementById("period_type").style.display = "block";
            document.getElementById("period_number").style.display = "block";
            document.getElementById("period_type_label_text").style.display = "block";
            document.getElementById("period_number_label_text").style.display = "block";
			return;
        }
        else if(nodes[x].checked && nodes[x].value == "transactional"){
            document.getElementById("period_type").style.display = "none";
            document.getElementById("period_number").style.display = "none";
            document.getElementById("period_type_label_text").style.display = "none";
            document.getElementById("period_number_label_text").style.display = "none";
			return;
        }
    }
}
var ibm_revenue_type_nodes = document.getElementsByName("revenue_type");
for(x in ibm_revenue_type_nodes){
	ibm_revenue_type_nodes[x].onclick = ibm_revenue_type_helper_function;
}
YAHOO.util.Event.onDOMReady(ibm_revenue_type_helper_function);
</script>
{/literal}
',
          ),
          
        ),        
        8 => 
        array (
          0 => array(
			'name' => 'name',
			'customLabel' => '',
			'customCode' => '<input type="hidden" name="ibm_revenud375unities_ida" id="ibm_revenud375unities_ida" value="{$smarty.request.ibm_revenud375unities_ida}">',
			),
          1 => 
          array (
            'name' => 'period_type',
            'studio' => 'visible',
            'customLabel' => '<span id=period_type_label_text>Period Type:</span>',
          ),
        ),
        9 => 
        array (
          0 => '',
          1 => 
          array (
            'name' => 'period_number',
            'label' => 'LBL_PERIOD_NUMBER',
            'customLabel' => '<span id=period_number_label_text>Period Number:</span>',
          ),
        ),
      ),
      'lbl_editview_panel1' => 
      array (
        0 => 
        array (
          0 => 
          array (
            'name' => 'platform',
            'studio' => 'visible',
            'label' => 'LBL_PLATFORM',
          ),
          1 =>
          array (
            'name' => 'financed_revenue_amount',
            'label' => 'LBL_FINANCED_REVENUE_AMOUNT',
            'customCode' => '
{counter name="panelFieldCount"}
{if strlen($fields.financed_revenue_amount.value) <= 0}
{assign var="value" value=$fields.financed_revenue_amount.default_value }
{else}
{assign var="value" value=$fields.financed_revenue_amount.value }
{/if}
<input type="text" name="{$fields.financed_revenue_amount.name}" id="{$fields.financed_revenue_amount.name}" size="30"  value="{sugar_number_format var=$value}" title="" tabindex="108" >
&nbsp;{$MOD.LBL_IN_CURRENCY}:&nbsp;<span id="fra_currency_id">{$fields.fra_currency_id.value}</span>
',
            ),
        ),
        1 => 
        array (
          0 => '',
/* 
          array (
            'name' => 'flow_code',
            'studio' => 'visible',
            'label' => 'LBL_FLOW_CODE',
          ),
*/
          1 => 
          array (
            'name' => 'igf_odds',
            'studio' => 'visible',
            'label' => 'LBL_IGF_ODDS',
          ),
        ),
        2 => 
        array (
          0 => 
          array (
            'name' => 'project_start_date',
            'label' => 'LBL_PROJECT_START_DATE',
          ),
          1 => 
          array (
            'name' => 'refurb',
            'label' => 'LBL_REFURB',
          ),
        ),
        3 => 
        array (
          0 => 
          array (
            'name' => 'project_end_date',
            'label' => 'LBL_PROJECT_END_DATE',
          ),
          1 => 
          array (
            'name' => 'team_name',
            'displayParams' => 
            array (
              'display' => true,
            ),
          ),
        ),
      ),
    ),
  ),
);
?>
