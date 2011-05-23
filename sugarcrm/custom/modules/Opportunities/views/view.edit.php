<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once('include/MVC/View/views/view.edit.php');

class OpportunitiesViewEdit extends ViewEdit {
        function OpportunitiesViewEdit(){
                parent::ViewEdit();
        }
        function preDisplay(){
				// BEGIN sadek - NEED TO SPLIT SOME LANGUAGE OPTIONS INTO SEPARATE FILES FOR PERFORMANCE
				$GLOBALS['app_list_strings']['bp_options'] = IBMHelper::getLargeEnum('bp_options');
				// END sadek - NEED TO SPLIT SOME LANGUAGE OPTIONS INTO SEPARATE FILES FOR PERFORMANCE

                // BEGIN rawb - This is where we load in the defaults, but only if they are editing a new record
                if ( empty($this->bean->id) || $this->bean->new_with_id ) {
                    $oppDefaultsJSON = $GLOBALS['current_user']->getPreference('TemplateFields','Opportunities');
                    if ( ! empty($oppDefaultsJSON) ) {
                        $oppDefaults = json_decode($oppDefaultsJSON,true);
                        if ( ! is_array($oppDefaults) ) { $oppDefaults = array(); }
                        foreach ( $oppDefaults as $key => $value ) {
                            $this->bean->$key = $value;
                            // $this->bean->field_defs[$key]['value'] = $value;
                        }
                        
                    }
                }
                // END rawb - defaults
                parent::preDisplay();
        }
	
	function display(){
		$canEditFinanceSalesStage = true;
		$canMoveSalesStage = true;
		
		if(empty($this->bean->id)){
			$canEditFinanceSalesStage = false;
			$canMoveSalesStage = false;
		}
		else{
			// If there are not any Global Financing (level 10) revenue line items, we don't allow them to push the sales stage
			$query = "SELECT count(*) AS count \n".
				 "FROM ibm_revenuepportunities_c rli_join \n".
				 "	 INNER JOIN ibm_revenuelineitems rli ON rli_join.ibm_revenu04e3neitems_idb = rli.id AND rli.deleted = 0 \n".
				 "WHERE rli_join.ibm_revenud375unities_ida = '{$this->bean->id}' AND rli_join.deleted = 0 \n".
				 "  AND rli.offering_type = 'B2000' "; // B2000 is Global Financing
			$res = $GLOBALS['db']->query($query);
			$row = $GLOBALS['db']->fetchByAssoc($res);
			
			if(isset($row['count']) && $row['count'] < 1){
				$canEditFinanceSalesStage = false;
			}
			
			// If there are NO revenue line items, can't move the opportunity sales stage past Identified
			$query = "SELECT count(*) AS count \n".
				 "FROM ibm_revenuepportunities_c rli_join \n".
				 "	 INNER JOIN ibm_revenuelineitems rli ON rli_join.ibm_revenu04e3neitems_idb = rli.id AND rli.deleted = 0 \n".
				 "WHERE rli_join.ibm_revenud375unities_ida = '{$this->bean->id}' AND rli_join.deleted = 0 \n";
			$res = $GLOBALS['db']->query($query);
			$row = $GLOBALS['db']->fetchByAssoc($res);
			
			if(isset($row['count']) && $row['count'] < 1){
				$canMoveSalesStage = false;
			}
		}
		
		if(!$canEditFinanceSalesStage){
			$js = <<<EOQ
<script type="text/javascript">
YUI().use('node-base', function(Y){
	Y.on('domready', function(){ new SUGAR.forms.VisibilityAction('financing_sales_stage_c','false', '').exec(); });
});
</script>
EOQ;
			echo $js;
		}
		
		echo '<script type="text/javascript" src="custom/include/javascript/IBM.js"></script>'."\n";
		if(!$canMoveSalesStage){
			$js = <<<EOQ
<script type="text/javascript">
YUI().use('node', 'node-base', function(Y){
	Y.on('domready', function(){
		var inputs = document.getElementsByTagName('input');
		for (var i = 0; i < inputs.length; i ++) {
			if (inputs[i].type == 'submit' && inputs[i].value == 'Save') {
				inputs[i].id = "tempIdString";
				inputs[i].onclick = function(){
					this.form.action.value='Save';
					var sodCheckResult = true;
					var ss_el = Y.one('#sales_stage');
					if(ss_el.get("selectedIndex") != 0){ // Identified
						add_error_style("EditView", "sales_stage", "Cannot move Sales Stage beyond Identified. No Revenue Line Items have been created for this Opportunity.", true);
						sodCheckResult = false;
					}
					if(sodCheckResult == false){
						return false;
					}
					else{
						var f = SUGAR.IBM.verifyFields('EditView');
						if(f == true){
							return true;
						}
						else{
							SUGAR.IBM.requiredHover(f);
							return false;
						}
					}
				}
			}
		}
	});
	
});
</script>
EOQ;
			echo $js;
		}
		else{
			$js = <<<EOQ
<script type="text/javascript">
YUI().use('node', 'node-base', function(Y){
	Y.on('domready', function(){
		var inputs = document.getElementsByTagName('input');
		for (var i = 0; i < inputs.length; i ++) {
			if (inputs[i].type == 'submit' && inputs[i].value == 'Save') {
				inputs[i].id = "tempIdString";
				inputs[i].onclick = function(){
					this.form.action.value='Save';
					var f = SUGAR.IBM.verifyFields('EditView');
					if(f == true){
						return true;
					}
					else{
						SUGAR.IBM.requiredHover(f);
						return false;
					}
				}
			}
		}
	});
	
});
</script>
EOQ;
			echo $js;
		}
		
		parent::display();
	}
}
