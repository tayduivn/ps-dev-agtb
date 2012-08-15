<?php
require_once('clients/summer/SideBarLayout.php');
$layout = new SideBarLayout();
$viewdefs['Tasks']['summer']['layout']['sidebar'] = $layout->getLayout();
