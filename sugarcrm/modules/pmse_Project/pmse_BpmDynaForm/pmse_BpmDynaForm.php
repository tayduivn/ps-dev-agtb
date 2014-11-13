<?php
/**
 * THIS CLASS IS FOR DEVELOPERS TO MAKE CUSTOMIZATIONS IN
 */
require_once('modules/pmse_Project/pmse_BpmDynaForm/pmse_BpmDynaForm_sugar.php');
class pmse_BpmDynaForm extends pmse_BpmDynaForm_sugar {

	/**
	 * This is a depreciated method, please start using __construct() as this method will be removed in a future version
     *
     * @see __construct
     * @depreciated
	 */
	function pmse_BpmDynaForm(){
		self::__construct();
	}

	public function __construct(){
		parent::__construct();
	}

}
?>