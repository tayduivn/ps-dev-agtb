<?php
$viewdefs = array (
  'Campaigns' => 
  array (
'QuickCreate' =>
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
        'javascript' => '<script type="text/javascript" src="include/javascript/popup_parent_helper.js?s={$SUGAR_VERSION}&c={$JS_CUSTOM_VERSION}"></script>
<script type="text/javascript">
function type_change() {ldelim}
	type = document.getElementsByName(\'campaign_type\');
	if(type[0].value==\'NewsLetter\') {ldelim}
		document.getElementById(\'freq_label\').style.display = \'\';
		document.getElementById(\'freq_field\').style.display = \'\';
	 {rdelim} else {ldelim}
		document.getElementById(\'freq_label\').style.display = \'none\';
		document.getElementById(\'freq_field\').style.display = \'none\';
	 {rdelim}
 {rdelim}
type_change();

function ConvertItems(id)  {ldelim}
	var items = new Array();

	//get the items that are to be converted
	expected_revenue = document.getElementById(\'expected_revenue\');
	budget = document.getElementById(\'budget\');
	actual_cost = document.getElementById(\'actual_cost\');
	expected_cost = document.getElementById(\'expected_cost\');

	//unformat the values of the items to be converted
	expected_revenue.value = unformatNumber(expected_revenue.value, num_grp_sep, dec_sep);
	expected_cost.value = unformatNumber(expected_cost.value, num_grp_sep, dec_sep);
	budget.value = unformatNumber(budget.value, num_grp_sep, dec_sep);
	actual_cost.value = unformatNumber(actual_cost.value, num_grp_sep, dec_sep);

	//add the items to an array
	items[items.length] = expected_revenue;
	items[items.length] = budget;
	items[items.length] = expected_cost;
	items[items.length] = actual_cost;

	//call function that will convert currency
	ConvertRate(id, items);

	//Add formatting back to items
	expected_revenue.value = formatNumber(expected_revenue.value, num_grp_sep, dec_sep);
	expected_cost.value = formatNumber(expected_cost.value, num_grp_sep, dec_sep);
	budget.value = formatNumber(budget.value, num_grp_sep, dec_sep);
	actual_cost.value = formatNumber(actual_cost.value, num_grp_sep, dec_sep);
 {rdelim}
</script>',
      ),
      'panels' => 
      array (
        'DEFAULT' => 
        array (
          0 => 
          array (
            0 => 
            array (
              'name' => 'name',
              'displayParams' => 
              array (
                'required' => true,
              ),
              'label' => 'LBL_CAMPAIGN_NAME',
            ),
            1 => 
            array (
              'name' => 'campaign_rating_c',
              'label' => 'campaign_rating_c',
            ),
          ),
          1 => 
          array (
            0 => 
            array (
              'name' => 'status',
              'displayParams' => 
              array (
                'required' => true,
              ),
              'label' => 'LBL_CAMPAIGN_STATUS',
            ),
            1 => 
            array (
              'name' => 'assigned_user_name',
              'label' => 'LBL_ASSIGNED_TO_NAME',
            ),
          ),
          2 => 
          array (
            0 => 
            array (
              'name' => 'start_date',
              'label' => 'LBL_CAMPAIGN_START_DATE',
            ),
            1 => 
            array (
              'name' => 'team_name',
              'displayParams' => 
              array (
                'display' => true,
              ),
              'label' => 'LBL_TEAM',
            ),
          ),
          3 => 
          array (
            0 => 
            array (
              'name' => 'end_date',
              'displayParams' => 
              array (
                'required' => true,
              ),
              'label' => 'LBL_CAMPAIGN_END_DATE',
            ),
            1 => NULL,
          ),
          4 => 
          array (
            0 => 
            array (
              'name' => 'campaign_type',
              'displayParams' => 
              array (
                'required' => true,
                'javascript' => 'onchange="type_change();"',
              ),
              'label' => 'LBL_CAMPAIGN_TYPE',
            ),
            1 => 
            array (
              'name' => 'product_offered_c',
              'label' => 'product_offered_c',
            ),
          ),
          5 => 
          array (
            0 => 
            array (
              'name' => 'media_c',
              'label' => 'media_c',
            ),
            1 => 
            array (
              'name' => 'offer_c',
              'label' => 'offer_c',
            ),
          ),
          6 => 
          array (
            0 => 
            array (
              'name' => 'contact_medium_c',
              'label' => 'contact_medium_c',
            ),
            1 => 
            array (
              'name' => 'offer_url_c',
              'label' => 'offer_url_c',
            ),
          ),
          7 => 
          array (
            0 => 
            array (
              'name' => 'target_audience_c',
              'label' => 'target_audience_c',
            ),
            1 => 
            array (
              'name' => 'message_c',
              'label' => 'message_c',
            ),
          ),
          8 => 
          array (
            0 => 
            array (
              'name' => 'currency_id',
              'label' => 'LBL_CURRENCY',
            ),
            1 => NULL,
          ),
          9 => 
          array (
            0 => NULL,
            1 => NULL,
          ),
          10 => 
          array (
            0 => 
            array (
              'name' => 'budget',
              'label' => 'LBL_CAMPAIGN_BUDGET',
            ),
            1 => 
            array (
              'name' => 'actual_cost',
              'label' => 'LBL_CAMPAIGN_ACTUAL_COST',
            ),
          ),
          11 => 
          array (
            0 => 
            array (
              'name' => 'expected_revenue',
              'label' => 'LBL_CAMPAIGN_EXPECTED_REVENUE',
            ),
            1 => 
            array (
              'name' => 'expected_cost',
              'label' => 'LBL_CAMPAIGN_EXPECTED_COST',
            ),
          ),
          12 => 
          array (
            0 => 
            array (
              'name' => 'impressions',
              'label' => 'LBL_CAMPAIGN_IMPRESSIONS',
            ),
            1 => NULL,
          ),
          13 => 
          array (
            0 => 
            array (
              'name' => 'objective',
              'displayParams' => 
              array (
                'rows' => 8,
                'cols' => 80,
              ),
              'label' => 'LBL_CAMPAIGN_OBJECTIVE',
            ),
          ),
          14 => 
          array (
            0 => 
            array (
              'name' => 'content',
              'displayParams' => 
              array (
                'rows' => 8,
                'cols' => 80,
              ),
              'label' => 'LBL_CAMPAIGN_CONTENT',
            ),
          ),
          15 => 
          array (
            0 => NULL,
          ),
          16 => 
          array (
            0 => 
            array (
              'name' => 'creative_c',
              'label' => 'creative_c',
            ),
            1 => 
            array (
              'name' => 'promo_url_c',
              'label' => 'promo_url_c',
            ),
          ),
          17 => 
          array (
            0 => NULL,
            1 => 
            array (
              'name' => 'reg_page_url_c',
              'label' => 'reg_page_url_c',
            ),
          ),
          18 => 
          array (
            0 => NULL,
            1 => 
            array (
              'name' => 'display_in_leads_dropdown_c',
              'label' => 'Display_in_Leads_Dropdown__c',
            ),
          ),
        ),
      ),
    ),
  ),
);
?>
