<?php

$layout = new GenericLayout();
//$layout->push('top', array('view'=>'subnav'));
// $layout->push('main',array('view'=>'agenda'));
$layout->push(array('layout'=>'sublist','context'=>array('link'=>'contacts')));
$viewdefs['Meetings']['summer']['layout']['sidebar'] = $layout->getLayout();
