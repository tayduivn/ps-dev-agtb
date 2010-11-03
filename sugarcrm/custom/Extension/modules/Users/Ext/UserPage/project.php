<?php

global $modules_exempt_from_availability_check;
$modules_exempt_from_availability_check=array('Holidays'=>'Holidays',);
$locked = false;
if(!empty($GLOBALS['sugar_config']['lock_subpanels'])){
	$locked = true;
}
$GLOBALS['sugar_config']['lock_subpanels'] = true;
$subpanel = new SubPanelTiles($focus, 'Users');

echo $subpanel->display(true,true);
$GLOBALS['sugar_config']['lock_subpanels'] = $locked;
?>
