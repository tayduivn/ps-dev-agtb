<?php
$viewdefs['Cases']['portal']['view']['list'] = array(
    'panels' =>
    array(
        0 =>
        array(
            'label' => 'LBL_PANEL_1',
            'fields' =>
            array(
                array(
                    'name' => 'case_number',
                    'width' =>  8,
                    'link' => true,
                    'label' => 'LBL_LIST_NUMBER',
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
                    'label' => 'LBL_TYPE',
                    'sorting' => true,
                    'enabled' => true,
                    'default' => true
                ),
                array (
                    'name' => 'date_entered',
                    'label' => 'LBL_LIST_DATE_CREATED',
                    'sorting' => true,
                    'enabled' => true,
                    'width' => 13,
                    'default' => true,
                ),
            ),
        ),
    ),
);

