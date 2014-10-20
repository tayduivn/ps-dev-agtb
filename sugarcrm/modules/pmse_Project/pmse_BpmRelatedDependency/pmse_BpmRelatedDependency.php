<?php
/**
 * THIS CLASS IS FOR DEVELOPERS TO MAKE CUSTOMIZATIONS IN
 */
require_once('modules/pmse_Project/pmse_BpmRelatedDependency/pmse_BpmRelatedDependency_sugar.php');
class pmse_BpmRelatedDependency extends pmse_BpmRelatedDependency_sugar {

	/**
	 * This is a depreciated method, please start using __construct() as this method will be removed in a future version
     *
     * @see __construct
     * @depreciated
	 */
	function pmse_BpmRelatedDependency(){
		self::__construct();
	}

	public function __construct(){
		parent::__construct();
	}

}
?>