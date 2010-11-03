<?php
class Utilities {
	function ChangeTitleBar(&$bean, $event, $arguments) {
		global $current_user;
		global $smartTitleBarExecuted;
		if(isset($_REQUEST['smartTitleBar']) && $_REQUEST['smartTitleBar'] == 'true'){
			$current_user->setPreference('smartTitleBar', true);
		}
		else if(isset($_REQUEST['smartTitleBar']) && $_REQUEST['smartTitleBar'] == 'false'){
			$current_user->setPreference('smartTitleBar', false);
		}
		if(isset($smartTitleBarExecuted) && $smartTitleBarExecuted){
			return;
		}
		if(!empty($current_user) && $current_user->getPreference('smartTitleBar') != true){
			return;
		}
		global $app_strings;
		global $sugar_config;
		global $current_language;
		
		//if there is nothing in the buffer then don't do anything
		//for some reason the program runs this hook twice, once before anything is rendered
		//and once after the page rendering has begun.
		if(isset($_REQUEST['module']) && $_REQUEST['module']==$bean->module_dir && empty($_REQUEST['to_pdf']) && isset($_REQUEST['action']) && $_REQUEST['action'] == 'DetailView') {
			$smartTitleBarExecuted = true;
			$this_mod_strings = return_module_language($current_language, $_REQUEST['module']);
			//Ok if this is the second go around then insert this javascript code
			echo "\n\n<SCRIPT LANGUAGE=\"JavaScript\">\n";
			echo "<!-- Hide this from older browsers\n";
			$output = '';
			if(!empty($_REQUEST['module'])){
				$output .= "document.title = '";
				switch($_REQUEST['module']){
					case 'Documents':
						$output .= $this_mod_strings['LBL_MODULE_NAME']." - ".str_replace("'", "\'", html_entity_decode($bean->document_name, ENT_QUOTES));
						break;
					case 'WorkFlowActionShells':
						$output .= "WorkFlowActionShells - ".str_replace("'", "\'", html_entity_decode($bean->action_type, ENT_QUOTES));
						break;
					default:
						$output .= $this_mod_strings['LBL_MODULE_NAME']." - ".str_replace("'", "\'", html_entity_decode($bean->name, ENT_QUOTES));
						break;
				}
				$output .= " :: ".$app_strings['LBL_BROWSER_TITLE']."';\n";
			}
			echo $output;
			echo "// end hide -->\n";
			echo "</SCRIPT>\n\n";
		}
	}
}
?>
