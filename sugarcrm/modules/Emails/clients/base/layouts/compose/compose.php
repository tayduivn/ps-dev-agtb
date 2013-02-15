<?php
$layout = MetaDataManager::getLayout('SideBarLayout');
$layout->push('main', array('view'=>'compose'));
$viewdefs['Emails']['base']['layout']['compose'] = $layout->getLayout();
