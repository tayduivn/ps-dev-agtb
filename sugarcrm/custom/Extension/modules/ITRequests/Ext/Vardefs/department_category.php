<?php
$dictionary["ITRequest"]["fields"]["department_category_c"]['visibility_grid'] = array(
    'trigger' => 'department_c',
    'values' => array(
        'internal' => array('IS_SugarInternal','IS_Demo','IS_dotCom','IS_Forums','IS_SugarShop','IS_ForgeExchange','IS_WikiBlogs'),
        'it' => array('IT_Computer','IT_Phone','IT_Software','IT_Credentials','IT_Purchasing','IT_Email','IT_Printing','IT_Network','IT_Employee','IT_Other'),
        'operations' => array('Ops_OnDemand','Ops_OnDemandSupport','Ops_SysService','Ops_SysSupport'),
    )
);


/*$dictionary["ITRequest"]["fields"]["project_c"]['visibility_grid'] = array(
    'trigger' => 'department_category_c',
    'values' => array(
        'IS_SugarInternal' => array('SI_Proj_GoGreen','SI_Proj_PLC','SI_Proj_MoofCart','SI_Proj_Other'),
    )
);*/


