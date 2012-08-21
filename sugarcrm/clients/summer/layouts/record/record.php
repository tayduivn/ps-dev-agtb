<?php

require_once('clients/summer/SideBarLayout.php');
$layout = new SideBarLayout();
$layout->push('main', array('view'=>'record'));
$viewdefs['summer']['layout']['record'] = $layout->getLayout();