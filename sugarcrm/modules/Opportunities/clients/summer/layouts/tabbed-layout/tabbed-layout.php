<?php
require_once('clients/summer/TabbedLayout.php');
$layout = new TabbedLayout();
$layout->push('main', array('view'=>'activitystream', 'label'=>'Activity Stream'));
$layout->push('main', array('layout'=>'list-cluster','label'=>'Related Contacts','context'=>array( 'link'=>'contacts')));


$viewdefs['Opportunities']['summer']['layout']['tabbed-layout'] = $layout->getLayout();