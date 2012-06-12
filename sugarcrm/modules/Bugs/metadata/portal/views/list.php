<?php
$viewdefs['Bugs']['portal']['view']['list'] = array(
    'panels' =>
    array(
        0 =>
        array(
            'label' => 'LBL_PANEL_1',
            'fields' =>
            array(
                array(
                    'name' => 'bug_number',
                    'width' =>  8,
                    'link' => true,
                    'label' => 'LBL_BUG_NUMBER',
                    'sorting' => true,
                    'enabled' => true,
                    'default' => true
                ),
                array(
                    'name' => 'name',
                    'width' =>  49,
                    'link' => true,
                    'label' => 'LBL_LIST_SUBJECT',
                    'sorting' => true,
                    'enabled' => true,
                    'default' => true
                ),
                array(                 
                    'name' => 'status',    
                    'width' =>  17,     
                    'label' => 'LBL_LIST_STATUS',
                    'sorting' => true,
                    'enabled' => true,
                    'default' => true
                ),
                array(
                    'name' => 'priority',
                    'width' =>  13,
                    'label' => 'LBL_LIST_PRIORITY',
                    'sorting' => true,
                    'enabled' => true,
                    'default' => true
                ),
                array(               
                    'name' => 'type',  
                    'width' =>  13,  
                    'label' => 'LBL_LIST_TYPE',
                    'sorting' => true,
                    'enabled' => true,
                    'default' => true
                ),
                array (
                    'name' => 'product_category',
                    'label' => 'LBL_PRODUCT_CATEGORY', 
                    'sorting' => true,
                    'enabled' => true,
                    'width' => 13,
                    'default' => true,
                ),
                array (
                    'name' => 'resolution',
                    'label' => 'LBL_RESOLUTION',
                    'sorting' => true,
                    'enabled' => true,
                    'width' => 13,
                    'default' => true,
                ),
                array (
                    'name' => 'date_entered',
                    'label' => 'LBL_DATE_ENTERED',
                    'sorting' => true,
                    'enabled' => true,
                    'width' => 13,
                    'default' => true,
                ),
            ),
        ),
    ),
);



