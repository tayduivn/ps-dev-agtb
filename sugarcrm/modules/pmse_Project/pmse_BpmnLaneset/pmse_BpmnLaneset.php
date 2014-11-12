<?php
/**
 * THIS CLASS IS FOR DEVELOPERS TO MAKE CUSTOMIZATIONS IN
 */
require_once('modules/pmse_Project/pmse_BpmnLaneset/pmse_BpmnLaneset_sugar.php');
class pmse_BpmnLaneset extends pmse_BpmnLaneset_sugar {

	/**
	 * This is a depreciated method, please start using __construct() as this method will be removed in a future version
     *
     * @see __construct
     * @depreciated
	 */
	function pmse_BpmnLaneset(){
		self::__construct();
	}

	public function __construct(){
		parent::__construct();
	}

}
?>