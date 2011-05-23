<?php
$viewdefs ['Accounts'] = 
array (
  'DetailView' => 
  array (
    'templateMeta' => 
    array (
      'form' => 
      array (
        'buttons' => 
        array (
          0 => 'EDIT',
          1 => 'DUPLICATE',
          2 => 'DELETE',
          3 => 'FIND_DUPLICATES',
          4 => 'CONNECTOR',
        ),
      ),
      'maxColumns' => '2',
      'useTabs' => true,
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
      'includes' => 
      array (
        0 => 
        array (
          'file' => 'modules/Accounts/Account.js',
        ),
      ),
    ),
    'panels' => 
    array (
      'lbl_account_information' => 
      array (
        0 => 
        array (
          0 => 
          array (
            'name' => 'name',
            'comment' => 'Name of the Company',
            'label' => 'LBL_NAME',
          	'customLabel' => '{if ! empty($fields.parent_id.value)}
          						<img src="themes/custom_images/cmr_icon.png" border="0" />
          						{else}
          						<img src="themes/custom_images/client_icon.png" border="0" />
          						{/if}
          						&nbsp;&nbsp;Name:',
            'displayParams' => 
            array (
              'enableConnectors' => true,
              'module' => 'Accounts',
              'connectors' => 
              array (
                0 => 'ext_rest_linkedin',
                1 => 'ext_rest_twitter',
              ),
            ),
          ),
          1 => 
          array (
            'name' => 'alt_lang_name',
            'comment' => 'Account alternate language name',
            'label' => 'LBL_ALT_ACCOUNT_NAME',
          ),
        ),
        1 => 
        array (
          0 => 
          array (
            'name' => 'website',
            'type' => 'link',
            'label' => 'LBL_WEBSITE',
            'displayParams' => 
            array (
              'link_target' => '_blank',
            ),
          ),
          1 =>
		  array(
			'name' => 'currency_id',
			'label' => 'LBL_CURRENCY',
		  ),
        ),
        2 => 
        array (
          0 => 
          array (
            'name' => 'billing_address_street',
            'label' => 'LBL_BILLING_ADDRESS',
            'type' => 'address',
            'displayParams' => 
            array (
              'key' => 'billing',
            ),
          ),
          1 => 
          array (
            'name' => 'shipping_address_street',
            'label' => 'LBL_SHIPPING_ADDRESS',
            'type' => 'address',
            'displayParams' => 
            array (
              'key' => 'shipping',
            ),
          ),
        ),
        3 => 
        array (
          0 => 
          array (
            'name' => 'client_id',
            'comment' => 'Account Client ID',
            'label' => 'LBL_CLIENT_ID',
            'customCode' => '{php}if(IBMHelper::isCMR($_REQUEST["record"])){ {/php}<a href=index.php?module=Accounts&action=DetailView&record={php}echo IBMHelper::getIDFromClientID($this->_tpl_vars["fields"]["client_id"]["value"]);{/php}>{$fields.client_id.value}</a>{php}}else if(IBMHelper::isClient($_REQUEST["record"])){ {/php}{$fields.client_id.value}{php} } {/php}',
          ),
          1 => 
          array (
            'name' => 'industry',
            'comment' => 'The company belongs in this industry',
            'label' => 'LBL_INDUSTRY',
          ),
        ),
        4 => 
        array (
          0 => 
          array (
            'name' => 'cmr_number',
            'label' => 'LBL_CMR_NUMBER',
            'customCode' => '{php}if(IBMHelper::isCMR($_REQUEST["record"])){ {/php}{$fields.cmr_number.value}
<script type="text/javascript">
YUI().use("node-base", function(Y) {literal}{{/literal} Y.on("domready", 
			function(){literal}{{/literal}
				if(document.getElementById("whole_subpanel_accounts") != null)
					document.getElementById("whole_subpanel_accounts").style.display = "none";
			{literal}}{/literal}
		)
	{literal}}{/literal}
);
</script>
{php}}else if(IBMHelper::isClient($_REQUEST["record"])){ {/php}<a href=index.php?module=Accounts&action=index&searchFormTab=basic_search&button=Search&client_id_basic={$fields.client_id.value}&query=true>View CMRs</a>{php} } {/php}',
          ),
          1 => 
          array (
            'name' => 'phone_office',
            'comment' => 'The office phone number',
            'label' => 'LBL_PHONE_OFFICE',
            'customCode' => '{if $fields.phone_office_suppressed.value == "1"}<strike>{/if}{$fields.phone_office.value}{if $fields.phone_office_suppressed.value == "1"}</strike>&nbsp;<i class="error">(Suppressed)</i>{/if}',
          ),
        ),
        5 => 
        array (
          0 => 
          array (
            'name' => 'tags',
          ),
          1 => 
          array (
            'name' => 'phone_fax',
            'comment' => 'The fax phone number of this company',
            'label' => 'LBL_FAX',
            'customCode' => '{if $fields.phone_fax_suppressed.value == "1"}<strike>{/if}{$fields.phone_fax.value}{if $fields.phone_fax_suppressed.value == "1"}</strike>&nbsp;<i class="error">(Suppressed)</i>{/if}',
          ),
        ),
		6 => array(
          0 => 
          array (
            'name' => 'assigned_user_name',
            'label' => 'LBL_ASSIGNED_TO',
          ),
			1 => array(

			),

          2 =>
          array (
            'name' => 'NEW_PANEL',
            'label' => 'LBL_DETAILVIEW_PANEL1',
			'default' => 'false',
          ),

		),
        7 => 
        array (
          0 => 
          array (
            'name' => 'coverage_id_c',
          ),
          1 => 
          array (
            'name' => 'duns_number_c',
          ),
        ),
        8 => 
        array (
          0 => 
          array (
            'name' => 'account_type',
            'comment' => 'The Company is of this type',
            'label' => 'LBL_TYPE',
          ),
          1 => 
          array (
            'name' => 'domestic_ultimate_duns_c',
          ),
        ),
        9 => 
        array (
          0 => 
          array (
            'name' => 'sic_code',
            'comment' => 'SIC code of the account',
            'label' => 'LBL_SIC_CODE',
          ),
          1 => 
          array (
            'name' => 'parent_hq_duns_c',
          ),
        ),
        10 => 
        array (
          0 => 
          array (
            'name' => 'global_client_id_c',
            'label' => 'LBL_GLOBAL_CLIENT_ID',
          ),
          1 => 
          array (
            'name' => 'global_ultimate_duns_c',
          ),
        ),
        11 => 
        array (
          0 => 
          array (
            'name' => 'bp_number_c',
          ),
          1 => '',

          2 =>
          array (
            'name' => 'NEW_PANEL',
            'label' => 'LBL_DETAILVIEW_PANEL2',
			'default' => 'false',
          ),

        ),
        12 => 
        array (
          0 => 
          array (
            'name' => 'last_interaction_c',
          ),
          1 => 
          array (
            'name' => 'language_c',
          ),
        ),
        13 => 
        array (
          0 => 
          array (
            'name' => 'confidence_grade_c',
          ),
          1 => 
          array (
            'name' => 'customer_buying_behavior_c',
          ),
        ),
        14 => 
        array (
          0 => 
          array (
            'name' => 'servicer_type_c',
          ),
          1 => '',
        ),
        15 => 
        array (
          0 => 
          array (
            'name' => 'sp_authorized_brand_c',
          ),
          1 => 
          array (
            'name' => 'quadrant_tier_c',
          ),
        ),
        16 => 
        array (
          0 => 
          array (
            'name' => 'account_status',
            'comment' => 'Account Status',
            'label' => 'LBL_ACCOUNT_STATUS',
          ),
          1 => 
          array (
            'name' => 'gb_rm_value_c',
            'label' => 'LBL_GB_RM_VALUE',
          ),
        ),
        17 => 
        array (
          0 => 
          array (
            'name' => 'issuing_country_name_c',
          ),
          1 => '',
        ),
        18 => 
        array (
          0 => 
          array (
            'name' => 'service_program_level_c',
          ),
          1 => '',
        ),
        19 => 
        array (
          0 => 
          array (
            'name' => 'trade_style_name_c',
          ),
          1 => '',
        ),
      ),

		'LBL_PANEL_clientWall' => 'custom/IBM_ISP/clientWall.php',
		'LBL_PANEL_installBase' => 'custom/IBM_ISP/installBase.php',
		'LBL_PANEL_clientSpend' => 'custom/IBM_ISP/clientSpend.php',
		'LBL_PANEL_news' => 'custom/IBM_ISP/news.php',
		'LBL_PANEL_clientIntel' => 'custom/IBM_ISP/clientIntel.php',
		'LBL_PANEL_references' => 'custom/IBM_ISP/references.php',

    ),
  ),
);
?>
