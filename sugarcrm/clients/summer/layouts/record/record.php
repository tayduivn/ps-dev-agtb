<?php

require_once('clients/summer/SideBarLayout.php');
$layout = new SideBarLayout();
$layout->push('top', array('view'=>'record'));
$viewdefs['summer']['layout']['record'] = $layout->getLayout();