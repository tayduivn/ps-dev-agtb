<?php
$isp_page = 'clientWall';
$tabIndex = 1;

$url = IBMHelper::getISPTargetURL($isp_page, $smarty->_tpl_vars['fields']);

$tab_content = IBMHelper::getISPTabContent('Accounts', $isp_page, $tabIndex, $url);

echo $tab_content;
