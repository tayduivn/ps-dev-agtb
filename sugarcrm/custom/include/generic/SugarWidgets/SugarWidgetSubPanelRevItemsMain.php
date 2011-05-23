<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once('include/generic/SugarWidgets/SugarWidgetField.php');

class SugarWidgetSubPanelRevItemsMain extends SugarWidgetField
{
	function displayList(&$layout_def)
	{
		$keys = array();
		
		/* No longer displaying level 10
		if(!empty($layout_def['fields']['OFFERING_TYPE'])){
			$keys[] = $layout_def['fields']['OFFERING_TYPE'];
		}
		*/
		if(!empty($layout_def['fields']['SUB_BRAND_C'])){
			$keys[] = $layout_def['fields']['SUB_BRAND_C'];
		}
		if(!empty($layout_def['fields']['BRAND_CODE'])){
			$keys[] = $layout_def['fields']['BRAND_CODE'];
		}
		if(!empty($layout_def['fields']['PRODUCT_INFORMATION'])){
			$keys[] = $layout_def['fields']['PRODUCT_INFORMATION'];
		}
		if(!empty($layout_def['fields']['MACHINE_TYPE'])){
			$keys[] = $layout_def['fields']['MACHINE_TYPE'];
		}
		
		if(empty($keys)){
			return "&nbsp;";
		}
		
		$display_vals = IBMHelper::getProductValuesFromKeys($keys);
		
		$return = "";
		/* No longer displaying level 10
		if(!empty($display_vals[$layout_def['fields']['OFFERING_TYPE']])){
			$return .= $display_vals[$layout_def['fields']['OFFERING_TYPE']];
		}
		*/
		if(!empty($display_vals[$layout_def['fields']['SUB_BRAND_C']])){
			$return .= "<BR>".$display_vals[$layout_def['fields']['SUB_BRAND_C']];
		}
		if(!empty($display_vals[$layout_def['fields']['BRAND_CODE']])){
			$return .= "<BR>".$display_vals[$layout_def['fields']['BRAND_CODE']];
		}
		if(!empty($display_vals[$layout_def['fields']['PRODUCT_INFORMATION']])){
			$return .= "<BR>".$display_vals[$layout_def['fields']['PRODUCT_INFORMATION']];
		}
		if(!empty($display_vals[$layout_def['fields']['MACHINE_TYPE']])){
			$return .= "<BR>".$display_vals[$layout_def['fields']['MACHINE_TYPE']];
		}
		return $return;
	}
}

?>
