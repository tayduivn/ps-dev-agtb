<?php
if($_REQUEST['module'] != 'ModuleBuilder') require_once('fonality/include/FONcall/FONcall.inc.php');

$listViewDefs ['Contacts'] = 
array (
  'NAME' => 
  array (
    'width' => '20%',
    'label' => 'LBL_LIST_NAME',
    'link' => true,
    'contextMenu' => 
    array (
      'objectType' => 'sugarPerson',
      'metaData' => 
      array (
        'contact_id' => '{$ID}',
        'module' => 'Contacts',
        'return_action' => 'ListView',
        'contact_name' => '{$FULL_NAME}',
        'parent_id' => '{$ACCOUNT_ID}',
        'parent_name' => '{$ACCOUNT_NAME}',
        'return_module' => 'Contacts',
        'parent_type' => 'Account',
        'notes_parent_type' => 'Account',
      ),
    ),
    'orderBy' => 'last_name',
    'default' => true,
    'related_fields' => 
    array (
      0 => 'first_name',
      1 => 'last_name',
      2 => 'salutation',
      3 => 'account_name',
      4 => 'account_id',
    ),
  ),
  'TITLE' => 
  array (
    'width' => '15%',
    'label' => 'LBL_LIST_TITLE',
    'default' => true,
  ),
  'ACCOUNT_NAME' => 
  array (
    'width' => '34%',
    'label' => 'LBL_LIST_ACCOUNT_NAME',
    'module' => 'Accounts',
    'id' => 'ACCOUNT_ID',
    'link' => true,
    'contextMenu' => 
    array (
      'objectType' => 'sugarAccount',
      'metaData' => 
      array (
        'return_module' => 'Contacts',
        'return_action' => 'ListView',
        'module' => 'Accounts',
        'parent_id' => '{$ACCOUNT_ID}',
        'parent_name' => '{$ACCOUNT_NAME}',
        'account_id' => '{$ACCOUNT_ID}',
        'account_name' => '{$ACCOUNT_NAME}',
      ),
    ),
    'default' => true,
    'sortable' => true,
    'ACLTag' => 'ACCOUNT',
    'related_fields' => 
    array (
      0 => 'account_id',
    ),
  ),
  'EMAIL1' => 
  array (
    'width' => '15%',
    'label' => 'LBL_LIST_EMAIL_ADDRESS',
    'link' => true,
    'customCode' => '{$EMAIL1_LINK}{$EMAIL1}</a>',
    'default' => true,
  ),
  'PORTAL_ACTIVE' => 
  array (
    'width' => '10%',
    'label' => 'LBL_PORTAL_ACTIVE',
    'default' => true,
  ),
  'PHONE_WORK' => 
  array (
    'width' => '15%',
    'label' => 'LBL_OFFICE_PHONE',
    'default' => true,
    'customCode' => '<script type="text/javascript">
			var phone_val = "{$PHONE_WORK}";
			if(phone_val != ""){
				document.write("<nofoncall>{$PHONE_WORK}</nofoncall> <a href=\\"javascript:void(1)\\" onclick=\\"ccall_number(\'{$PHONE_WORK}\',\'Contacts\',\'{$ID}\',\'{$ID}\',\'UpgradeWizard_commit\',\'false\');\\"><img title=\\"Call using the Fonality phone system\\" border=\\"0\\" src=\\"fonality/include/images/dial.jpg\\" align=\\"absmiddle\\"></a>");
			}
			</script>',
  ),
  'TEAM_NAME' => 
  array (
    'width' => '10%',
    'label' => 'LBL_LIST_TEAM',
    'default' => true,
  ),
  'ASSIGNED_USER_NAME' => 
  array (
    'width' => '10%',
    'label' => 'LBL_LIST_ASSIGNED_USER',
    'default' => true,
  ),
  'DEPARTMENT' => 
  array (
    'width' => '10%',
    'label' => 'LBL_DEPARTMENT',
    'default' => false,
  ),
  'DO_NOT_CALL' => 
  array (
    'width' => '10%',
    'label' => 'LBL_DO_NOT_CALL',
    'default' => false,
  ),
  'PHONE_HOME' => 
  array (
    'width' => '10%',
    'label' => 'LBL_HOME_PHONE',
    'default' => false,
    'customCode' => '<script type="text/javascript">
			var phone_val = "{$PHONE_HOME}";
			if(phone_val != ""){
				document.write("<nofoncall>{$PHONE_HOME}</nofoncall> <a href=\\"javascript:void(1)\\" onclick=\\"ccall_number(\'{$PHONE_HOME}\',\'Contacts\',\'{$ID}\',\'{$ID}\',\'UpgradeWizard_commit\',\'false\');\\"><img title=\\"Call using the Fonality phone system\\" border=\\"0\\" src=\\"fonality/include/images/dial.jpg\\" align=\\"absmiddle\\"></a>");
			}
			</script>',
  ),
  'PHONE_MOBILE' => 
  array (
    'width' => '10%',
    'label' => 'LBL_MOBILE_PHONE',
    'default' => false,
    'customCode' => '<script type="text/javascript">
			var phone_val = "{$PHONE_MOBILE}";
			if(phone_val != ""){
				document.write("<nofoncall>{$PHONE_MOBILE}</nofoncall> <a href=\\"javascript:void(1)\\" onclick=\\"ccall_number(\'{$PHONE_MOBILE}\',\'Contacts\',\'{$ID}\',\'{$ID}\',\'UpgradeWizard_commit\',\'false\');\\"><img title=\\"Call using the Fonality phone system\\" border=\\"0\\" src=\\"fonality/include/images/dial.jpg\\" align=\\"absmiddle\\"></a>");
			}
			</script>',
  ),
  'PHONE_OTHER' => 
  array (
    'width' => '10%',
    'label' => 'LBL_OTHER_PHONE',
    'default' => false,
    'customCode' => '<script type="text/javascript">
			var phone_val = "{$PHONE_OTHER}";
			if(phone_val != ""){
				document.write("<nofoncall>{$PHONE_OTHER}</nofoncall> <a href=\\"javascript:void(1)\\" onclick=\\"ccall_number(\'{$PHONE_OTHER}\',\'Contacts\',\'{$ID}\',\'{$ID}\',\'UpgradeWizard_commit\',\'false\');\\"><img title=\\"Call using the Fonality phone system\\" border=\\"0\\" src=\\"fonality/include/images/dial.jpg\\" align=\\"absmiddle\\"></a>");
			}
			</script>',
  ),
  'PHONE_FAX' => 
  array (
    'width' => '10%',
    'label' => 'LBL_FAX_PHONE',
    'default' => false,
    'customCode' => '<script type="text/javascript">
			var phone_val = "{$PHONE_FAX}";
			if(phone_val != ""){
				document.write("<nofoncall>{$PHONE_FAX}</nofoncall> <a href=\\"javascript:void(1)\\" onclick=\\"ccall_number(\'{$PHONE_FAX}\',\'Contacts\',\'{$ID}\',\'{$ID}\',\'UpgradeWizard_commit\',\'false\');\\"><img title=\\"Call using the Fonality phone system\\" border=\\"0\\" src=\\"fonality/include/images/dial.jpg\\" align=\\"absmiddle\\"></a>");
			}
			</script>',
  ),
  'EMAIL2' => 
  array (
    'width' => '15%',
    'label' => 'LBL_LIST_EMAIL_ADDRESS',
    'customCode' => '{$EMAIL2_LINK}{$EMAIL2}</a>',
    'default' => false,
  ),
  'EMAIL_OPT_OUT' => 
  array (
    'width' => '10%',
    'label' => 'LBL_EMAIL_OPT_OUT',
    'default' => false,
  ),
  'PRIMARY_ADDRESS_STREET' => 
  array (
    'width' => '10%',
    'label' => 'LBL_PRIMARY_ADDRESS_STREET',
    'default' => false,
  ),
  'PRIMARY_ADDRESS_CITY' => 
  array (
    'width' => '10%',
    'label' => 'LBL_PRIMARY_ADDRESS_CITY',
    'default' => false,
  ),
  'PRIMARY_ADDRESS_STATE' => 
  array (
    'width' => '10%',
    'label' => 'LBL_PRIMARY_ADDRESS_STATE',
    'default' => false,
  ),
  'PRIMARY_ADDRESS_POSTALCODE' => 
  array (
    'width' => '10%',
    'label' => 'LBL_PRIMARY_ADDRESS_POSTALCODE',
    'default' => false,
  ),
  'ALT_ADDRESS_COUNTRY' => 
  array (
    'width' => '10%',
    'label' => 'LBL_ALT_ADDRESS_COUNTRY',
    'sortable' => false,
    'default' => false,
  ),
  'ALT_ADDRESS_STREET' => 
  array (
    'width' => '10%',
    'label' => 'LBL_ALT_ADDRESS_STREET',
    'default' => false,
  ),
  'ALT_ADDRESS_CITY' => 
  array (
    'width' => '10%',
    'label' => 'LBL_ALT_ADDRESS_CITY',
    'default' => false,
  ),
  'ALT_ADDRESS_STATE' => 
  array (
    'width' => '10%',
    'label' => 'LBL_ALT_ADDRESS_STATE',
    'default' => false,
  ),
  'ALT_ADDRESS_POSTALCODE' => 
  array (
    'width' => '10%',
    'label' => 'LBL_ALT_ADDRESS_POSTALCODE',
    'default' => false,
  ),
  'DATE_ENTERED' => 
  array (
    'width' => '10%',
    'label' => 'LBL_DATE_ENTERED',
    'default' => false,
  ),
  'CREATED_BY_NAME' => 
  array (
    'width' => '10%',
    'label' => 'LBL_CREATED',
    'default' => false,
  ),
  'MODIFIED_USER_NAME' => 
  array (
    'width' => '10%',
    'label' => 'LBL_MODIFIED',
    'default' => false,
  ),
);
?>
