<?php
/**
 * THIS CLASS IS FOR DEVELOPERS TO MAKE CUSTOMIZATIONS IN
 */
require_once('modules/pmse_Project/pmse_BpmnDocumentation/pmse_BpmnDocumentation_sugar.php');
class pmse_BpmnDocumentation extends pmse_BpmnDocumentation_sugar {

	/**
	 * This is a depreciated method, please start using __construct() as this method will be removed in a future version
     *
     * @see __construct
     * @depreciated
	 */
	function pmse_BpmnDocumentation(){
		self::__construct();
	}

	public function __construct(){
		parent::__construct();
	}

}
?>