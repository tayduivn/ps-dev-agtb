<?php
$layout = MetaDataManager::getLayout('GenericLayout');
$layout->push(array('view'=>'preview'));
$viewdefs['Leads']['base']['layout']['preview'] = $layout->getLayout();
