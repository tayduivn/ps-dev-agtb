<?php
require_once ('modules/ModuleBuilder/MB/ModuleBuilder.php');
require_once ('modules/ModuleBuilder/parsers/ParserFactory.php');
require_once ('modules/ModuleBuilder/parsers/views/DeployedMetaDataImplementation.php');
require_once ('fonality/include/normalizePhone/utils.php');
require_once ('ModuleInstall/ModuleInstaller.php');
session_start();

global $sugar_config;

$phone_modules = array("Contacts", "Accounts", "Leads", "Prospects");

$varmap = array (
    	MB_EDITVIEW => 'EditView' ,
    	MB_DETAILVIEW => 'DetailView' ,
    	MB_QUICKCREATE => 'QuickCreate'
);

print ("<h3>Repair Layout Status</h3>");

foreach($phone_modules as $mod){
	print("Repairing $mod DetailView...");
	ob_flush();
	flush();
	
	$parser = new DeployedMetaDataImplementation ( 'detailview', $mod, $varmap ) ;
	$defs = $parser->getViewdefs();

	$bean_name = substr($mod, 0, -1);
	$bean = new $bean_name();
	$phone_fields = getAllPhoneFields($bean);
	
	// put custom code to phone fields
	foreach($defs['DetailView']['panels'] as $ind => $panel){
		if(!empty($panel)){
			if(is_int($ind)){
				foreach($panel as $col => $cols){
					if(is_array($cols)){
						if(in_array($cols['name'], $phone_fields)){
							$defs['DetailView']['panels'][$ind][$col]['customCode'] = "{fonality_phone value=\$fields.".$cols['name'].".value this_module=".$mod." this_id=\$fields.id.value}";
						}
						// replace (filler) with NULL
						if($cols['name'] == '(filler)'){
							$defs['DetailView']['panels'][$ind][$col] = NULL;
						}
						// unset (empty)
						if($cols['name'] == '(empty)'){
							unset($defs['DetailView']['panels'][$ind][$col]);
						}
					} else {
						if(in_array($cols, $phone_fields)){
							$label = $bean->field_defs[$cols]['vname'];
							$defs['DetailView']['panels'][$ind][$col] = array(
								'name' => $cols,
								'label' => $label,
								'customCode' => "{fonality_phone value=\$fields.".$cols.".value this_module=".$mod." this_id=\$fields.id.value}"
							);
						}
					}
				}
			} else {
				foreach($panel as $row => $rows){
					foreach($rows as $col => $cols){
						if(is_array($cols)){
							if(in_array($cols['name'], $phone_fields)){
								$defs['DetailView']['panels'][$ind][$row][$col]['customCode'] = "{fonality_phone value=\$fields.".$cols['name'].".value this_module=".$mod." this_id=\$fields.id.value}";
							}
							// replace (filler) with NULL
							if($cols['name'] == '(filler)'){
								$defs['DetailView']['panels'][$ind][$row][$col] = NULL;
							}
							// unset (empty)
							if($cols['name'] == '(empty)'){
								unset($defs['DetailView']['panels'][$ind][$row][$col]);
							}
						} else {
							if(in_array($cols, $phone_fields)){
								$label = $bean->field_defs[$cols]['vname'];
								$defs['DetailView']['panels'][$ind][$row][$col] = array(
									'name' => $cols,
									'label' => $label,
									'customCode' => "{fonality_phone value=\$fields.".$cols.".value this_module=".$mod." this_id=\$fields.id.value}"
								);
							}
						}
					}
				}
			}
		}
	}

	$parser->deploy ( $defs ) ;
	
	print(" Done<br>");
	print("Repairing $mod ListView...");
	ob_flush();
	flush();
	
	$parser = ParserFactory::getParser('listview', $mod, null, null);
	$defs = $parser->_viewdefs;

	foreach($defs as $col => $cols){
		if(in_array($col, $phone_fields)){
			$contact_id = $mod == "Contacts" ? "{\$ID}" : "";
			$defs[$col]['customCode'] = '<script type="text/javascript">
			var phone_val = "{$'.strtoupper($col).'}";
			if(phone_val != ""){
				document.write("<nofoncall>{$'.strtoupper($col).'}</nofoncall> <a href=\"javascript:void(1)\" onclick=\"ccall_number(\'{$'.strtoupper($col).'}\',\''.$mod.'\',\'{$ID}\',\''.$contact_id.'\',\''.$_REQUEST['action'].'\',\'false\');\"><img title=\"Call using the Fonality phone system\" border=\"0\" src=\"fonality/include/images/dial.jpg\" align=\"absmiddle\"></a>");
			}
			</script>';
		}
	}
	
	// fixup defs
	foreach($defs as $col => $cols){
		$fieldname = strtoupper($col);
		$defs[$fieldname] = $cols;
		unset($defs[$col]);
	}
	
	$parser->_viewdefs = $defs;
	$parser->handleSave(false);
	
	$def_file = DeployedMetaDataImplementation::getFileName("listview", $mod);
	
	// add the required javascript file
	$content = file_get_contents($def_file);
	$fh = @fopen($def_file, 'w');
	$header = "<?php\n";
	$header .= "if(\$_REQUEST['module'] != 'ModuleBuilder') require_once('fonality/include/FONcall/FONcall.inc.php');\n";
	fwrite($fh, $header. str_replace("<?php", "", $content));
	fclose($fh);
	
	print(" Done<br>");
}

print("Repairing Accounts Subpanels...");
ob_flush();
flush();

require_once ('include/SubPanel/SubPanel.php');
$account_subpanels = array("Contacts","Accounts","Leads");
foreach($account_subpanels as $mod){
	$bean_name = substr($mod, 0, -1);
	$bean = new $bean_name();
	$phone_fields = getAllPhoneFields($bean);
	
	$parser = ParserFactory::getParser('listview', 'Accounts', null, strtolower($mod));
	$defs = $parser->_viewdefs;
	foreach($defs as $col => $cols){
		if(in_array($col, $phone_fields)){
			$defs[$col]['widget_class'] = 'Fieldfonalityphone';
		}
	}
	
	$parser->_viewdefs = $defs;
	$parser->handleSave(false);
}

print(" Done<br>");
print("Repairing Contacts Subpanels...");
ob_flush();
flush();
$contact_subpanels = array("Contacts","Leads");
foreach($contact_subpanels as $mod){
	$bean_name = substr($mod, 0, -1);
	$bean = new $bean_name();
	$phone_fields = getAllPhoneFields($bean);
	
	$parser = ParserFactory::getParser('listview', 'Contacts', null, strtolower($mod));
	$defs = $parser->_viewdefs;
	foreach($defs as $col => $cols){
		if(in_array($col, $phone_fields)){
			$defs[$col]['widget_class'] = 'Fieldfonalityphone';
		}
	}
	$parser->_viewdefs = $defs;
	$parser->handleSave(false);
}
print(" Done<br>");
print("Repairing Cases Subpanels...");
ob_flush();
flush();
$case_subpanels = array("Contacts");
foreach($case_subpanels as $mod){
	$bean_name = substr($mod, 0, -1);
	$bean = new $bean_name();
	$phone_fields = getAllPhoneFields($bean);
	
	$parser = ParserFactory::getParser('listview', 'Cases', null, strtolower($mod));
	$defs = $parser->_viewdefs;

	foreach($defs as $col => $cols){
		if(in_array($col, $phone_fields)){
			$defs[$col]['widget_class'] = 'Fieldfonalityphone';
		}
	}
	$parser->_viewdefs = $defs;
	$parser->handleSave(false);
}
print(" Done<br>");
print("Repairing Opportunities Subpanels...");
ob_flush();
flush();
$opp_subpanels = array("Contacts","Leads");
foreach($opp_subpanels as $mod){
	$bean_name = substr($mod, 0, -1);
	$bean = new $bean_name();
	$phone_fields = getAllPhoneFields($bean);
	
	$parser = ParserFactory::getParser('listview', 'Opportunities', null, strtolower($mod));
	$defs = $parser->_viewdefs;

	foreach($defs as $col => $cols){
		if(in_array($col, $phone_fields)){
			$defs[$col]['widget_class'] = 'Fieldfonalityphone';
		}
	}
	$parser->_viewdefs = $defs;
	$parser->handleSave(false);
}
print(" Done<br>");
print("Checking Fonality Custom Fields...<br/>");
ob_flush();
flush();
$custom_field_mods = array("Contacts", "Accounts", "Leads");
$new_custom_fields = array();
foreach($custom_field_mods as $mod){
	$bean_name = substr($mod, 0, -1);
	$bean = new $bean_name();
	$phone_fields = getAllPhoneFields($bean);
	$all_fields = array();
	foreach($bean->field_defs as $def){
		$all_fields[] = $def['name'];
	}
	
	// create custom fields as necessary
	foreach($phone_fields as $ph){
		$normalized_field = $ph."_normalized_c";
		if(!in_array($normalized_field, $all_fields)){
			print "Adding $mod Normalized ".$ph." custom field<br/>";
			$new_custom_fields[] = array(
				'id' => $mod.$normalized_field,
				'name' => $normalized_field,
				'label' => "Normalized $ph",
				'type' => 'varchar',
				'max_size' => 50,
				'require_option' => 'optional',
				'default_value' => NULL,
				'module' => $mod
			);
		}
	}
}
if(!empty($new_custom_fields)){
	print("Creating Fonality custom fields...");
	ob_flush();
	flush();
	$mod_installer = new ModuleInstaller();
	$mod_installer->install_custom_fields($new_custom_fields);
	
	// force clear vardefs cache
	require_once('modules/Administration/QuickRepairAndRebuild.php');
	$repair = new RepairAndClear();
	$repair->show_output = false;
	$repair->module_list = array('Account','Contact','Lead');
	$repair->clearVardefs();
	$tmp = $GLOBALS['reload_vardefs'];
	$GLOBALS['reload_vardefs'] = 1;
	
	print(" Done<br/>");
	print("Updating phone numbers... ");
	ob_flush();
	flush();
	require_once('uae_initial_normalize.php');
	$_SESSION['initial_normalize'] = 1;
	$GLOBALS['reload_vardefs'] = $tmp;
}
print("<br/>Done");
?>
