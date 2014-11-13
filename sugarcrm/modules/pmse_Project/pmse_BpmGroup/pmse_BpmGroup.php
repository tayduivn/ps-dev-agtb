<?php
/**
 * THIS CLASS IS FOR DEVELOPERS TO MAKE CUSTOMIZATIONS IN
 */
require_once('modules/pmse_Project/pmse_BpmGroup/pmse_BpmGroup_sugar.php');
class pmse_BpmGroup extends pmse_BpmGroup_sugar {

	/**
	 * This is a depreciated method, please start using __construct() as this method will be removed in a future version
     *
     * @see __construct
     * @depreciated
	 */
	function pmse_BpmGroup(){
		self::__construct();
	}

	public function __construct(){
		parent::__construct();
	}

}
?>