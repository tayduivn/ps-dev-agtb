<?php
$layout = new GenericLayout();
$layout->push(array('view'=>'activitystream', 'label'=>'Activity Stream'));
$layout->push(array('layout'=>'list-cluster','label'=>'Related Contacts','context'=>array( 'link'=>'contacts')));


$viewdefs['Opportunities']['summer']['layout']['tabbed-layout'] = $layout->getLayout();
