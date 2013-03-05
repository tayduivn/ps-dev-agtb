<?php

$layout = MetaDataManager::getLayout('GenericLayout');
$layout->push(array('layout'=>'list-cluster','label'=>'Related Contacts','context'=>array( 'link'=>'contacts')));

$viewdefs['Opportunities']['summer']['layout']['tabbed-layout'] = $layout->getLayout();
