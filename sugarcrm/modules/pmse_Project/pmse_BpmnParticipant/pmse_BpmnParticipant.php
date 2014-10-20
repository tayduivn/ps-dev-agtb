<?php
/**
 * THIS CLASS IS FOR DEVELOPERS TO MAKE CUSTOMIZATIONS IN
 */
require_once('modules/pmse_Project/pmse_BpmnParticipant/pmse_BpmnParticipant_sugar.php');
class pmse_BpmnParticipant extends pmse_BpmnParticipant_sugar {

	/**
	 * This is a depreciated method, please start using __construct() as this method will be removed in a future version
     *
     * @see __construct
     * @depreciated
	 */
	function pmse_BpmnParticipant(){
		self::__construct();
	}

	public function __construct(){
		parent::__construct();
	}

}
?>