<?php 
 //WARNING: The contents of this file are auto-generated


if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

//auto-generated file DO NOT EDIT
$layout_defs['Accounts']['subpanel_setup']['contacts']['override_subpanel_name'] = 'Accountdefault';


//auto-generated file DO NOT EDIT
$layout_defs['Accounts']['subpanel_setup']['opportunities']['override_subpanel_name'] = 'Accountdefault';


//auto-generated file DO NOT EDIT
$layout_defs['Accounts']['subpanel_setup']['contacts']['override_subpanel_name'] = 'AccountForAccounts';


if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

//auto-generated file DO NOT EDIT
$layout_defs['Accounts']['subpanel_setup']['opportunities']['override_subpanel_name'] = 'Accountdefault';



$layout_defs['Accounts']['subpanel_setup']['subscriptions'] =  array(
            'order' => 73,
            'module' => 'Subscriptions',
            'sort_order' => 'desc',
            'sort_by' => 'expiration_date',
            'get_subpanel_data' => 'subscriptions',
            'add_subpanel_data' => 'subscription_id',
            'subpanel_name' => 'default',
            'title_key' => 'LBL_SUBSCRIPTIONS_SUBPANEL_TITLE',
            'top_buttons' => array(
                //array('widget_class' => 'SubPanelTopSelectButton'),
            ),
        );




$layout_defs['Accounts']['subpanel_setup']['itrequests'] =  array(
            'order' => 83,
            'module' => 'ITRequests',
            'sort_order' => 'asc',
            'sort_by' => 'date_modified',
            'get_subpanel_data' => 'itrequests',
            'add_subpanel_data' => 'itrequest_id',
            'subpanel_name' => 'default',
            'title_key' => 'LBL_ITREQUESTS_SUBPANEL_TITLE',
            'top_buttons' => array(
                array('widget_class' => 'SubPanelTopCreateButton'),
                array('widget_class' => 'SubPanelTopSelectButton'),
            ),
        );



if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

//auto-generated file DO NOT EDIT
$layout_defs['Accounts']['subpanel_setup']['sugar_installations']['override_subpanel_name'] = 'Accountdefault';


if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

$layout_defs['Accounts']['subpanel_setup']['history']['collection_list']['calls']['override_subpanel_name'] = "ForHistory";


// SADEK - BEGIN SUGARINTERNAL CUSTOMIZATION - Change quick create on accounts opportunities subpanel to a full create form
$layout_defs['Accounts']['subpanel_setup']['opportunities']['top_buttons'][0]['widget_class'] = 'SubPanelTopCreateButton';



// created: 2009-02-13 17:29:10
$layout_defs["Accounts"]["subpanel_setup"]["opportunities_accounts"] = array (
  'order' => 100,
  'module' => 'Opportunities',
  'subpanel_name' => 'ForAccounts',
  'sort_order' => 'asc',
  'sort_by' => 'id',
  'title_key' => 'LBL_OPPORTUNITIES_ACCOUNTS_FROM_OPPORTUNITIES_TITLE',
  'get_subpanel_data' => 'opportunities_accounts',
  'top_buttons' => 
  array (
    0 => 
    array (
      'widget_class' => 'SubPanelTopCreateButton',
    ),
    1 => 
    array (
      'widget_class' => 'SubPanelTopSelectButton',
      'popup_module' => 'Opportunities',
      'mode' => 'MultiSelect',
    ),
  ),
);



if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

$layout_defs['Accounts']['subpanel_setup']['cases']['sort_order'] = 'desc';
$layout_defs['Accounts']['subpanel_setup']['cases']['sort_by'] = 'cases.date_entered';
$layout_defs['Accounts']['subpanel_setup']['bugs']['sort_order'] = 'desc';
$layout_defs['Accounts']['subpanel_setup']['bugs']['sort_by'] = 'bugs.date_entered';


if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

//auto-generated file DO NOT EDIT
$layout_defs['Accounts']['subpanel_setup']['cases']['override_subpanel_name'] = 'Accountdefault';



                //BEGIN Sugar Interal customizations
/* SADEK 2008-04-03 - REMOVED SUBPANEL SINCE WE NO LONGER USE THIS MODULE
$layout_defs['Accounts']['subpanel_setup']['download_keys'] = 
                array(
                        'order' => 65,
                        'module' => 'DownloadKeys',
                        'sort_order' => 'asc',
                        'sort_by' => 'download_key',
                        'subpanel_name' => 'default',
                        'get_subpanel_data' => 'download_keys',
                        'add_subpanel_data' => 'download_key_id',
                        'title_key' => 'LBL_DOWNLOAD_KEYS_SUBPANEL_TITLE',
                        'top_buttons' => array(
                                array('widget_class' => 'SubPanelTopCreateButton'),
                        ),
                );
*/

$layout_defs['Accounts']['subpanel_setup']['sugar_installations'] = 
                array(
                        'order' => 66,
                        'sort_order' => 'asc',
                        'sort_by' => 'status',
                        'module' => 'SugarInstallations',
                        'subpanel_name' => 'default',
                        'get_subpanel_data' => 'sugar_installations',
                        'add_subpanel_data' => 'sugar_installation_id',
                        'title_key' => 'LBL_SUGAR_INSTALLATIONS_SUBPANEL_TITLE',
                        'top_buttons' => array(),
                );
                //END Sugar Interal customizations





if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');







$layout_defs['Accounts']['subpanel_setup']['history']['collection_list']['linkedemails_contacts'] =  array(
	                'module' => 'Emails',
	                'subpanel_name' => 'ForUnlinkedEmailHistory',
	                'get_subpanel_data' => 'function:get_unlinked_email_query_via_link',
	    		    'function_parameters' => array('import_function_file' => 'modules/SNIP/utils.php', 'link' => 'contacts'),
	                'generate_select'=>true,
				    'get_distinct_data' => true,
);


// created: 2010-07-27 14:40:36
$layout_defs["Accounts"]["subpanel_setup"]["accounts_orders"] = array (
  'order' => 100,
  'module' => 'Orders',
  'subpanel_name' => 'default',
  'sort_order' => 'asc',
  'sort_by' => 'id',
  'title_key' => 'LBL_ACCOUNTS_ORDERS_FROM_ORDERS_TITLE',
  'get_subpanel_data' => 'accounts_orders',
  'top_buttons' => 
  array (
    0 => 
    array (
      'widget_class' => 'SubPanelTopButtonQuickCreate',
    ),
    1 => 
    array (
      'widget_class' => 'SubPanelTopSelectButton',
      'mode' => 'MultiSelect',
    ),
  ),
);


//auto-generated file DO NOT EDIT
$layout_defs['Accounts']['subpanel_setup']['contacts']['override_subpanel_name'] = 'AccountForAccounts';


//auto-generated file DO NOT EDIT
$layout_defs['Accounts']['subpanel_setup']['opportunities']['override_subpanel_name'] = 'AccountForAccounts';


//auto-generated file DO NOT EDIT
$layout_defs['Accounts']['subpanel_setup']['leads']['override_subpanel_name'] = 'Accountdefault';


//auto-generated file DO NOT EDIT
$layout_defs['Accounts']['subpanel_setup']['cases']['override_subpanel_name'] = 'AccountForAccounts';


//auto-generated file DO NOT EDIT
$layout_defs['Accounts']['subpanel_setup']['subscriptions']['override_subpanel_name'] = 'Accountdefault';


//auto-generated file DO NOT EDIT
$layout_defs['Accounts']['subpanel_setup']['accounts']['override_subpanel_name'] = 'Accountdefault';


//auto-generated file DO NOT EDIT
$layout_defs['Accounts']['subpanel_setup']['leads']['override_subpanel_name'] = 'Account';


//auto-generated file DO NOT EDIT
$layout_defs['Accounts']['subpanel_setup']['sugar_installations']['override_subpanel_name'] = 'Accountdefault';


//auto-generated file DO NOT EDIT
$layout_defs['Accounts']['subpanel_setup']['accounts_orders']['override_subpanel_name'] = 'Accountdefault';

?>