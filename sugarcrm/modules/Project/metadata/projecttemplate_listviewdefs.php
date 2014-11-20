<?php
$listViewDefs['ProjectTemplates'] = array(
    'NAME' => array(
        'width' => '40',
        'label' => 'LBL_LIST_NAME',
        'link' => true,
        'default' => true,
        'customCode'=>'<a href="index.php?offset={$OFFSET}&record={$ID}&action=ProjectTemplatesDetailView&module=Project" >{$NAME}</a>'),
    'ESTIMATED_START_DATE' => array(
        'width' => '20',
        'label' => 'LBL_DATE_START',
        'link' => false,
        'default' => true),
    'ESTIMATED_END_DATE' => array(
        'width' => '20',
        'label' => 'LBL_DATE_END',
        'link' => false,
        'default' => true),
    'TEAM_NAME' => array(
        'width' => '2',
        'label' => 'LBL_LIST_TEAM',
        'related_fields' => array('team_id'),
        'default' => false),
);