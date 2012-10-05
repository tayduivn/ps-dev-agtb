<?php
require_once('clients/summer/GenericLayout.php');
$layout = new GenericLayout();
$layout->push(array('view' => 'createhelp'));
$viewdefs['Opportunities']['summer']['layout']['new-sidebar'] = $layout->getLayout();