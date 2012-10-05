<?php
require_once('clients/summer/GenericLayout.php');
$layout = new GenericLayout();
$layout->push(array('view' => 'createhelp'));
$viewdefs['Contacts']['summer']['layout']['new-sidebar'] = $layout->getLayout();
