<?php

$layout = new SideBarLayout();
$layout->push('main', array('view'=>'record'));
$layout->push('main', array('layout'=>'tabbed-layout'));
$layout->push('side', array('layout'=>'sidebar'));
$viewdefs['summer']['layout']['record'] = $layout->getLayout();
