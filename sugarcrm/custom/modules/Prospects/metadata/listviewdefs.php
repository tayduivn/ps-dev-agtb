<?php
if($_REQUEST['module'] != 'ModuleBuilder') require_once('fonality/include/FONcall/FONcall.inc.php');

$listViewDefs ['Prospects'] = 
array (
  'FULL_NAME' => 
  array (
    'width' => '25',
    'label' => 'LBL_LIST_NAME',
    'link' => true,
    'related_fields' => 
    array (
      0 => 'first_name',
      1 => 'last_name',
    ),
    'orderBy' => 'last_name',
    'default' => true,
  ),
  'TITLE' => 
  array (
    'width' => '20',
    'label' => 'LBL_LIST_TITLE',
    'link' => false,
    'default' => true,
  ),
  'EMAIL1' => 
  array (
    'width' => '20',
    'label' => 'LBL_LIST_EMAIL_ADDRESS',
    'sortable' => false,
    'link' => false,
    'default' => true,
  ),
  'PHONE_WORK' => 
  array (
    'width' => '10',
    'label' => 'LBL_LIST_PHONE',
    'link' => false,
    'default' => true,
    'customCode' => '<script type="text/javascript">
			var phone_val = "{$PHONE_WORK}";
			if(phone_val != ""){
				document.write("<nofoncall>{$PHONE_WORK}</nofoncall> <a href=\\"javascript:void(1)\\" onclick=\\"ccall_number(\'{$PHONE_WORK}\',\'Prospects\',\'{$ID}\',\'\',\'UpgradeWizard_commit\',\'false\');\\"><img title=\\"Call using the Fonality phone system\\" border=\\"0\\" src=\\"fonality/include/images/dial.jpg\\" align=\\"absmiddle\\"></a>");
			}
			</script>',
  ),
);
?>
