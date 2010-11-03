<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

$layout_defs['Contacts']['subpanel_setup']['cases']['sort_order'] = 'desc';
$layout_defs['Contacts']['subpanel_setup']['cases']['sort_by'] = 'cases.date_entered';
$layout_defs['Contacts']['subpanel_setup']['bugs']['sort_order'] = 'desc';
$layout_defs['Contacts']['subpanel_setup']['bugs']['sort_by'] = 'bugs.date_entered';
?>