<?php
require_once('modules/pmse_Inbox/pmse_Inbox_sugar.php');

class pmse_Inbox extends pmse_Inbox_sugar
{

    /**
     * This is a depreciated method, please start using __construct() as this method will be removed in a future version
     *
     * @see __construct
     * @depreciated
     */
    function pmse_Inbox()
    {
        self::__construct();
    }

    public function __construct()
    {
        parent::__construct();
    }

}