<?PHP

require_once('modules/pmse_Project/pmse_Project_sugar.php');

class pmse_Project extends pmse_Project_sugar
{

    /**
     * This is a depreciated method, please start using __construct() as this method will be removed in a future version
     *
     * @see __construct
     * @depreciated
     */
    function pmse_Project()
    {
        self::__construct();
    }

    public function __construct()
    {
        parent::__construct();
    }

}

?>