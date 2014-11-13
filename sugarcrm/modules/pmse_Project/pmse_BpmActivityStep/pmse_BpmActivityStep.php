<?php
/**
 * THIS CLASS IS FOR DEVELOPERS TO MAKE CUSTOMIZATIONS IN
 */
require_once('modules/pmse_Project/pmse_BpmActivityStep/pmse_BpmActivityStep_sugar.php');
class pmse_BpmActivityStep extends pmse_BpmActivityStep_sugar {

	/**
	 * This is a depreciated method, please start using __construct() as this method will be removed in a future version
     *
     * @see __construct
     * @depreciated
	 */
	function pmse_BpmActivityStep(){
		self::__construct();
	}

	public function __construct(){
		parent::__construct();
	}

}
?>