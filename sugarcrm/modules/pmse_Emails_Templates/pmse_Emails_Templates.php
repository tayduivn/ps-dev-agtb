<?php

require_once('modules/pmse_Emails_Templates/pmse_Emails_Templates_sugar.php');
class pmse_Emails_Templates extends pmse_Emails_Templates_sugar {

	/**
	 * This is a depreciated method, please start using __construct() as this method will be removed in a future version
     *
     * @see __construct
     * @depreciated
	 */
	function pmse_Emails_Templates(){
		self::__construct();
	}

	public function __construct(){
		parent::__construct();
	}
	
}
?>