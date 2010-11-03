<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once('include/MVC/View/views/view.edit.php');

class BugsViewEdit extends ViewEdit {
	function BugsViewEdit(){
		parent::ViewEdit();
	}
	function display(){
		global $app_list_strings;
		global $mod_strings;
		
		$javascript_function = <<<EOQ
		<script language="javascript">
		var contribution_value_before = document.getElementById('contribution_agreement_c');
		document.getElementById('fix_proposed_c').onchange = function(){
			if(document.getElementById('fix_proposed_c').checked == false){
				//document.getElementById('contribution_agreement_c').style.display = "none";
				contribution_value_before = document.getElementById('contribution_agreement_c').value;
				document.getElementById('contribution_agreement_c').value = '';
				document.getElementById('contribution_agreement_c').disabled = true;
			}
			else{
				//document.getElementById('contribution_agreement_c').style.display = "block";
				document.getElementById('contribution_agreement_c').value = contribution_value_before;
				document.getElementById('contribution_agreement_c').disabled = false;
			}
		}
		if(document.getElementById('fix_proposed_c').checked == false){
			//document.getElementById('contribution_agreement_c').style.display = "none";
			contribution_value_before = document.getElementById('contribution_agreement_c').value;
			document.getElementById('contribution_agreement_c').value = '';
			document.getElementById('contribution_agreement_c').disabled = true;
		}
		else{
			//document.getElementById('contribution_agreement_c').style.display = "block";
			document.getElementById('contribution_agreement_c').value = contribution_value_before;
			document.getElementById('contribution_agreement_c').disabled = false;
		}
		</script>
EOQ;
		
		parent::display();
		echo "\n".$javascript_function."\n";
	}
}
