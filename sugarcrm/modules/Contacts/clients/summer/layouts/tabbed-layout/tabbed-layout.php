<?php

$layout = MetaDataManager::getLayout('GenericLayout');
$layout->push(array('layout'=>'list-cluster','label'=>'Related Opportunities', 'context'=>array('link'=>'opportunities')));
$viewdefs['Contacts']['summer']['layout']['tabbed-layout'] = $layout->getLayout();
