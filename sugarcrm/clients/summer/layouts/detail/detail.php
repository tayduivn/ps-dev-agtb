<?php

$layout = new SideBarLayout();
$layout->push('main', array('view'=>'detail'));
//$layout->push('side', array('layout'=>'sidebar'));
$viewdefs['summer']['layout']['detail'] = $layout->getLayout();
