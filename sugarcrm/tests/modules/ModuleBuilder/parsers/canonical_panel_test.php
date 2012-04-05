<?php
/**
 * User: rbacon
 * Date: 2012.04.03
 * Time: 16:51
 * just require this file into the unit test. and then use some iterator in the data provider.
 */
$canonicals = array(
// canonical panels
array(array('label' => 'Default', 'fields' => array())),

array(array('label' => 'Default',
            'fields' => array(
                array(
                    'name' => 'name',
                    'label' => 'Name',
                ),
                array(
                    'name' => 'status',
                    'label' => 'Status',
                ),
                array(
                    'name' => 'description',
                    'label' => 'Description',
                ),
                ""
))),


array(array('label' => 'Default',
            'fields' => array(
                array(
                    'name' => 'name',
                    'label' => 'Name',
                    'displayParams' => array('colspan' => 2)
                ),
                array(
                    'name' => 'status',
                    'label' => 'Status',
                ),
                array(
                    'name' => 'description',
                    'label' => 'Description',
                ),

))),








);