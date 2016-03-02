<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
require_once('include/SugarFields/Parsers/MetaParser.php');
	class InlineEdit{


		 private function getHTML(){
			return getVersionedScript('include/EditView/InlineEdit.js').
			"<link rel='stylesheet' type='text/css' href='".getVersionedPath('include/EditView/InlineEdit.css')."></link>";

		}

		private function getEditInPlace($panels, $bean){
					$fields = array();
					if (!MetaParser::hasMultiplePanels($panels)) {
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
