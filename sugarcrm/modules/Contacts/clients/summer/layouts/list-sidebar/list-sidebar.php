<?php

$layout = MetaDataManager::getLayout('GenericLayout');
// will remove this later, for now treat this as a no-op view :)
$layout->push(array('view'=>'maps'));
$viewdefs['Contacts']['summer']['layout']['list-sidebar'] = $layout->getLayout();
