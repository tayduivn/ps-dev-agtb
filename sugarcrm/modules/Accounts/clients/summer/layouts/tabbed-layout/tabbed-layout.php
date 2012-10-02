<?php
require_once('clients/summer/TabbedLayout.php');
$layout = new TabbedLayout();
$layout->push('main', array('view'=>'activitystream', 'label'=>'Activity Stream'));
$layout->push('main', array('layout'=>'list-cluster','label'=>'Related Contacts', 'context'=>array( 'link'=>'contacts')));
$layout->push('main', array('layout'=>'list-cluster', 'label'=>'Related Opportunities', 'context'=>array( 'link'=>'opportunities')));

$viewdefs['Accounts']['summer']['layout']['tabbed-layout'] = $layout->getLayout();