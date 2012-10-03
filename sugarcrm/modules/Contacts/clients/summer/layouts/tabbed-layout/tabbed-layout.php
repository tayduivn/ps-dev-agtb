<?php

$layout = new GenericLayout();
$layout->push(array('view'=>'activitystream', 'label'=>'Activity Stream'));
$layout->push(array('layout'=>'list-cluster','label'=>'Related Opportunities', 'context'=>array('link'=>'opportunities')));
$viewdefs['Contacts']['summer']['layout']['tabbed-layout'] = $layout->getLayout();
