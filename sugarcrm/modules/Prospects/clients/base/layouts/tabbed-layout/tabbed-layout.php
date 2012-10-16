<?php

$layout = MetaDataManager::getLayout('GenericLayout');
//$layout->push(array('view'=>'activitystream', 'label'=>'Activity Stream'));
//$layout->push(array('layout'=>'list-cluster','label'=>'Related Contacts', 'context'=>array( 'link'=>'contacts')));
//$layout->push(array('layout'=>'list-cluster', 'label'=>'Related Opportunities', 'context'=>array( 'link'=>'opportunities')));

$viewdefs['Prospects']['base']['layout']['tabbed-layout'] = $layout->getLayout();
