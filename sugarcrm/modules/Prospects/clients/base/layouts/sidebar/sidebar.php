<?php

$layout = MetaDataManager::getLayout('GenericLayout');
//$layout->push(array('view'=>'interactions'));
//$layout->push(array('view'=>'maps'));

$viewdefs['Prospects']['base']['layout']['sidebar'] = $layout->getLayout();
