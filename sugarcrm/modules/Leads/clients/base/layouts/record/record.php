<?php

$layout = MetaDataManager::getLayout('SideBarLayout');
$layout->push("main",array(
        'layout'=> array(
            'type' => 'drawer',
            'showEvent' => array(
                "delegate" => true,
                "event" => "click [name=lead_convert_button]",
            ),
            'components' => array(
                array(
                    'layout' => 'convert',
                )
            )
        ),
    ));
$layout->push('main', array('view'=>'record'));
$layout->push('main', array('view'=>'convert-results'));
$layout->push('main', array('layout'=>'subpanel'));
$layout->push('side', array('layout'=>'sidebar'));
$layout->push('preview', array('layout' => 'preview'));
$viewdefs['Leads']['base']['layout']['record'] = $layout->getLayout();
