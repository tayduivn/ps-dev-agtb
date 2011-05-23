<?php
$isp_page = 'clientSpend';
$tabIndex = 3;

$url = IBMHelper::getISPTargetURL($isp_page, $smarty->_tpl_vars['fields']);

$tab_content = IBMHelper::getISPTabContent('Accounts', $isp_page, $tabIndex, $url, 700);

echo $tab_content;
