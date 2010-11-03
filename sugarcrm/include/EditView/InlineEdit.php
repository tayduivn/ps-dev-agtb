<?php
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
require_once('include/SugarFields/Parsers/MetaParser.php');
	class InlineEdit{
		
		
		 private function getHTML(){
			return " 
					<script src='include/EditView/InlineEdit.js'></script>
					<link rel='stylesheet' type='text/css' href='include/EditView/InlineEdit.css'></link>
";
			
		}
		
		private function getEditInPlace($panels, $bean){
					$fields = array();
					$parser = new MetaParser();
					if(!$parser->hasMultiplePanels($panels)){ 
						$panels = array($panels);
					}
					foreach($panels as $panel){
						foreach($panel as $row){
						foreach($row as $field){
							if(is_array($field)){
								$field_name = $field['name'];
							}else{
								$field_name = $field;
							}
							if(isset($bean->field_defs[$field_name]) && empty($bean->field_defs[$field_name]['source'])){
								switch($bean->field_defs[$field_name]['type']){
									case 'int':
									case 'phone':
									case 'float':
									case 'varchar':
									case 'name':
										$fields[$field_name] = array('editInPlace'=>true, 'type'=>'varchar');
										break;
									case 'enum':
										$fields[$field_name] = array('editInPlace'=>true, 'type'=>'enum');
										break;
								
									case 'bool':
										$fields[$field_name] = array('editInPlace'=>true, 'type'=>'checkbox');
										break;
									case 'date':
										$fields[$field_name] = array('editInPlace'=>true, 'type'=>'date');
										break;
									case 'text':
										$fields[$field_name] = array('editInPlace'=>true, 'type'=>'text');
						
										break;
										
								}
							}
						}
					
				}
					}
				return $fields;
		}
		
		public function getEditInPlaceJS($panels, $bean){
			$fields = $this->getEditInPlace($panels, $bean);
			$json_fields = json_encode($fields);
			return $this->getHTML() .  <<<EOQ
<script>
	YUI().use('node-base', function(Y) {
     function init() {
     	//console.log('ready');
        InlineEditor.markListAsEditable($json_fields);
     }
     Y.on("domready", init);
		});

</script>
EOQ;

		}
	}
?>