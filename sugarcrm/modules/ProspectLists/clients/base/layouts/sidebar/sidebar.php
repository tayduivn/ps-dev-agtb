<?php

$layout = MetaDataManager::getLayout('GenericLayout');
//$layout->push(array('view'=>'interactions'));
//$layout->push(array('view'=>'maps'));

$viewdefs['ProspectLists']['base']['layout']['sidebar'] = $layout->getLayout();
