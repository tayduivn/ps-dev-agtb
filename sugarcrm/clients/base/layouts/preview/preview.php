<?php

$layout = MetaDataManager::getLayout('GenericLayout', array('type' => 'preview'));
$layout->push(array("view" => "preview-header"));
$layout->push(array("view" => "preview"));
$layout->push(array(
        "layout"  => "preview-activitystream",
        'context' => array(
            'module'   => 'Activities',
            'forceNew' => true,
        ),
    ));
$viewdefs['base']['layout']['preview'] = $layout->getLayout();
