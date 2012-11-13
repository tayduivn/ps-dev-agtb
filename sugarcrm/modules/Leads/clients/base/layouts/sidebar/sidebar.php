<?php
$layout = MetaDataManager::getLayout('GenericLayout');
$layout->push(array('view'=>'placeholder'));
$viewdefs['Leads']['base']['layout']['sidebar'] = $layout->getLayout();
