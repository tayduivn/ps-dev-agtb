<?php
//FILE SUGARCRM flav=ent || flav=sales ONLY
$layout = MetaDataManager::getLayout('SideBarLayout');
$layout->push('main', array('view'=>'record'));
$layout->push('preview', array('layout' => 'preview'));
$viewdefs['Contacts']['portal']['layout']['record'] = $layout->getLayout();
