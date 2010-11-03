<?php
$admin_option_defs=array();
$admin_option_defs['Administration']['dial'] = array('Calls','LBL_DIAL_TITLE','LBL_DIAL_DESCRIPTION','index.php?module=Administration&action=ConfigureDialSettings');
$admin_option_defs['Administration']['call_assistant'] = array('Calls','LBL_CA_TITLE','LBL_CA_DESCRIPTION','index.php?module=Administration&action=ConfigureCASettings');
$admin_option_defs['Administration']['repair_clicktodial_layout'] = array('Repair','LBL_RL_TITLE','LBL_RL_DESCRIPTION','index.php?module=Administration&action=repair_clicktodial_layouts');
$admin_option_defs['Administration']['uae_support'] = array('support_icon','LBL_UAE_SUPPORT_TITLE','LBL_UAE_SUPPORT_DESCRIPTION','index.php?module=Administration&action=uae_support');
$admin_option_defs['Administration']['pbx_settings'] = array('fonuae_PBXSettings','LBL_PBX_SETTINGS_TITLE','LBL_PBX_SETTINGS_DESCRIPTION','index.php?module=fonuae_PBXSettings&action=index');
$admin_group_header[]=array('UAE_ADMIN','',false,$admin_option_defs,'LBL_UAE_ADMIN_DESC');
?>
