<?php
/**
 * THIS CLASS IS FOR DEVELOPERS TO MAKE CUSTOMIZATIONS IN
 */
require_once('modules/pmse_Project/pmse_BpmnGateway/pmse_BpmnGateway_sugar.php');
class pmse_BpmnGateway extends pmse_BpmnGateway_sugar {

	/**
	 * This is a depreciated method, please start using __construct() as this method will be removed in a future version
     *
     * @see __construct
     * @depreciated
	 */
	function pmse_BpmnGateway(){
		self::__construct();
	}

	public function __construct(){
		parent::__construct();
	}

}
?>