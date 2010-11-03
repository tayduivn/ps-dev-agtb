<?php
if($_REQUEST['module'] != 'ModuleBuilder') require_once('fonality/include/FONcall/FONcall.inc.php');

$listViewDefs ['Leads'] = 
array (
  'NAME' => 
  array (
    'width' => '30',
    'label' => 'LBL_LIST_NAME',
    'link' => true,
    'default' => true,
    'related_fields' => 
    array (
      0 => 'first_name',
      1 => 'last_name',
      2 => 'salutation',
    ),
  ),
  'STATUS' => 
  array (
    'width' => '10',
    'label' => 'LBL_LIST_STATUS',
    'default' => true,
  ),
  'ACCOUNT_NAME' => 
  array (
    'width' => '20',
    'label' => 'LBL_LIST_ACCOUNT_NAME',
    'default' => true,
  ),
  'EMAIL1' => 
  array (
    'width' => '15',
    'label' => 'LBL_LIST_EMAIL_ADDRESS',
    'customCode' => '{$EMAIL1_LINK}{$EMAIL1}</a>',
    'default' => true,
  ),
  'TEAM_NAME' => 
  array (
    'width' => '10',
    'label' => 'LBL_LIST_TEAM',
    'default' => true,
  ),
  'ASSIGNED_USER_NAME' => 
  array (
    'width' => '10',
    'label' => 'LBL_LIST_ASSIGNED_USER',
    'default' => true,
  ),
  'LEAD_RELATION_C' => 
  array (
    'width' => '10',
    'label' => 'Lead_Role_c',
    'sortable' => false,
    'default' => true,
  ),
  'LEAD_SCORE' => 
  array (
    'width' => '10',
    'label' => 'LBL_LEAD_SCORE',
    'customCode' => '<a href="index.php?module=Leads&action=LeadScoreDetails&lead_id={$ID}&to_pdf=1" onclick="window.open(this.href,\'window\',\'width=950,height=400,resizable,menubar\'); return false;">{$LEAD_SCORE}</a>',
    'default' => true,
  ),
  'PHONE_WORK' => 
  array (
    'width' => '15',
    'label' => 'LBL_LIST_PHONE',
    'default' => false,
    'customCode' => '<script type="text/javascript">
			var phone_val = "{$PHONE_WORK}";
			if(phone_val != ""){
				document.write("<nofoncall>{$PHONE_WORK}</nofoncall> <a href=\\"javascript:void(1)\\" onclick=\\"ccall_number(\'{$PHONE_WORK}\',\'Leads\',\'{$ID}\',\'\',\'UpgradeWizard_commit\',\'false\');\\"><img title=\\"Call using the Fonality phone system\\" border=\\"0\\" src=\\"fonality/include/images/dial.jpg\\" align=\\"absmiddle\\"></a>");
			}
			</script>',
  ),
  'TITLE' => 
  array (
    'width' => '10',
    'label' => 'LBL_TITLE',
    'default' => false,
  ),
  'REFERED_BY' => 
  array (
    'width' => '10',
    'label' => 'LBL_REFERED_BY',
    'default' => false,
  ),
  'LEAD_SOURCE' => 
  array (
    'width' => '10',
    'label' => 'LBL_LEAD_SOURCE',
    'default' => false,
  ),
  'DEPARTMENT' => 
  array (
    'width' => '10',
    'label' => 'LBL_DEPARTMENT',
    'default' => false,
  ),
  'DO_NOT_CALL' => 
  array (
    'width' => '10',
    'label' => 'LBL_DO_NOT_CALL',
    'default' => false,
  ),
  'PHONE_HOME' => 
  array (
    'width' => '10',
    'label' => 'LBL_HOME_PHONE',
    'default' => false,
    'customCode' => '<script type="text/javascript">
			var phone_val = "{$PHONE_HOME}";
			if(phone_val != ""){
				document.write("<nofoncall>{$PHONE_HOME}</nofoncall> <a href=\\"javascript:void(1)\\" onclick=\\"ccall_number(\'{$PHONE_HOME}\',\'Leads\',\'{$ID}\',\'\',\'UpgradeWizard_commit\',\'false\');\\"><img title=\\"Call using the Fonality phone system\\" border=\\"0\\" src=\\"fonality/include/images/dial.jpg\\" align=\\"absmiddle\\"></a>");
			}
			</script>',
  ),
  'PHONE_MOBILE' => 
  array (
    'width' => '10',
    'label' => 'LBL_MOBILE_PHONE',
    'default' => false,
    'customCode' => '<script type="text/javascript">
			var phone_val = "{$PHONE_MOBILE}";
			if(phone_val != ""){
				document.write("<nofoncall>{$PHONE_MOBILE}</nofoncall> <a href=\\"javascript:void(1)\\" onclick=\\"ccall_number(\'{$PHONE_MOBILE}\',\'Leads\',\'{$ID}\',\'\',\'UpgradeWizard_commit\',\'false\');\\"><img title=\\"Call using the Fonality phone system\\" border=\\"0\\" src=\\"fonality/include/images/dial.jpg\\" align=\\"absmiddle\\"></a>");
			}
			</script>',
  ),
  'PHONE_OTHER' => 
  array (
    'width' => '10',
    'label' => 'LBL_OTHER_PHONE',
    'default' => false,
    'customCode' => '<script type="text/javascript">
			var phone_val = "{$PHONE_OTHER}";
			if(phone_val != ""){
				document.write("<nofoncall>{$PHONE_OTHER}</nofoncall> <a href=\\"javascript:void(1)\\" onclick=\\"ccall_number(\'{$PHONE_OTHER}\',\'Leads\',\'{$ID}\',\'\',\'UpgradeWizard_commit\',\'false\');\\"><img title=\\"Call using the Fonality phone system\\" border=\\"0\\" src=\\"fonality/include/images/dial.jpg\\" align=\\"absmiddle\\"></a>");
			}
			</script>',
  ),
  'PHONE_FAX' => 
  array (
    'width' => '10',
    'label' => 'LBL_FAX_PHONE',
    'default' => false,
    'customCode' => '<script type="text/javascript">
			var phone_val = "{$PHONE_FAX}";
			if(phone_val != ""){
				document.write("<nofoncall>{$PHONE_FAX}</nofoncall> <a href=\\"javascript:void(1)\\" onclick=\\"ccall_number(\'{$PHONE_FAX}\',\'Leads\',\'{$ID}\',\'\',\'UpgradeWizard_commit\',\'false\');\\"><img title=\\"Call using the Fonality phone system\\" border=\\"0\\" src=\\"fonality/include/images/dial.jpg\\" align=\\"absmiddle\\"></a>");
			}
			</script>',
  ),
  'EMAIL2' => 
  array (
    'width' => '15',
    'label' => 'LBL_LIST_EMAIL_ADDRESS',
    'customCode' => '{$EMAIL2_LINK}{$EMAIL2}</a>',
    'default' => false,
  ),
  'LEAD_RATING_C' => 
  array (
    'width' => '10',
    'label' => 'Lead_Rating_c',
    'sortable' => false,
    'default' => false,
  ),
  'EMAIL_OPT_OUT' => 
  array (
    'width' => '10',
    'label' => 'LBL_EMAIL_OPT_OUT',
    'default' => false,
  ),
  'PRIMARY_ADDRESS_COUNTRY' => 
  array (
    'width' => '10',
    'label' => 'LBL_PRIMARY_ADDRESS_COUNTRY',
    'default' => false,
  ),
  'PRIMARY_ADDRESS_STREET' => 
  array (
    'width' => '10',
    'label' => 'LBL_PRIMARY_ADDRESS_STREET',
    'default' => false,
  ),
  'PRIMARY_ADDRESS_CITY' => 
  array (
    'width' => '10',
    'label' => 'LBL_PRIMARY_ADDRESS_CITY',
    'default' => false,
  ),
  'PRIMARY_ADDRESS_STATE' => 
  array (
    'width' => '10',
    'label' => 'LBL_PRIMARY_ADDRESS_STATE',
    'default' => false,
  ),
  'PRIMARY_ADDRESS_POSTALCODE' => 
  array (
    'width' => '10',
    'label' => 'LBL_PRIMARY_ADDRESS_POSTALCODE',
    'default' => false,
  ),
  'ALT_ADDRESS_COUNTRY' => 
  array (
    'width' => '10',
    'label' => 'LBL_ALT_ADDRESS_COUNTRY',
    'default' => false,
  ),
  'ALT_ADDRESS_STREET' => 
  array (
    'width' => '10',
    'label' => 'LBL_ALT_ADDRESS_STREET',
    'default' => false,
  ),
  'ALT_ADDRESS_CITY' => 
  array (
    'width' => '10',
    'label' => 'LBL_ALT_ADDRESS_CITY',
    'default' => false,
  ),
  'ALT_ADDRESS_STATE' => 
  array (
    'width' => '10',
    'label' => 'LBL_ALT_ADDRESS_STATE',
    'default' => false,
  ),
  'ALT_ADDRESS_POSTALCODE' => 
  array (
    'width' => '10',
    'label' => 'LBL_ALT_ADDRESS_POSTALCODE',
    'default' => false,
  ),
  'DATE_ENTERED' => 
  array (
    'width' => '10',
    'label' => 'LBL_DATE_ENTERED',
    'default' => false,
  ),
  'CREATED_BY' => 
  array (
    'width' => '10',
    'label' => 'LBL_CREATED',
    'default' => false,
  ),
  'MODIFIED_USER_NAME' => 
  array (
    'width' => '10',
    'label' => 'LBL_MODIFIED',
    'default' => false,
  ),
);
?>
