<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
 */
global $mod_strings,$db;

require_once('modules/Score/Score.php');
require_once('include/JSON.php');
$json = new JSON();
$json->use = JSON_LOOSE_TYPE;
$data = $json->decode(html_entity_decode($_REQUEST['scoreThis']));

if ( $_REQUEST['offset'] == -1 ) {
    $newData = array();
    foreach ( $data as $module => $recordCount ) {
        $newData[$module] = array('offset'=>0,
                                  'remaining'=>$recordCount);
    }
    echo('<h2>'.$mod_strings['LBL_RESCORE_STARTING'].'</h2>');
    echo("<script type='text/javascript'>document.location='index.php?module=Score&action=ProcessRescore&offset=0&total=".$_REQUEST['total']."&to_pdf=1&scoreThis=".$json->encode($newData)."';</script>");
    return;
}

if ( count($data) < 1 ) {
	echo("<h2>".$mod_strings['LBL_RESCORE_DONE']."</h2>");
	echo("<script type='text/javascript'>parent.document.location='index.php?module=Score&action=ManualRescore'</script>");
	return;
}

printf("<h2>%03.2f%%<br>%d / %d</h2>",(float)($_REQUEST['offset']/$_REQUEST['total'])*100,$_REQUEST['offset'],$_REQUEST['total']);
flush();


$tmp = array_keys($data);
$module = $tmp[0];
$numRecords = $data[$module]['remaining'];
$moduleOffset = $data[$module]['offset'];
if ( $moduleOffset == 0 ) {
    // It's the first time we've seen this module, dump out the old records from the score table
    // echo("Delete: for module $module<br>");
    $db->query("DELETE FROM score WHERE source_module = '".$db->quote($module)."'",true);
}
$recordsOnPage = min(10000,$data[$module]['remaining']);
// echo("Scoring: $recordsOnPage records for module $module<br>");
$bean = loadBean($module);
score::sqlRescoreChunk($module,$bean,'',$data[$module]['offset'],$recordsOnPage);
$data[$module]['remaining'] -= $recordsOnPage;
$data[$module]['offset'] = $moduleOffset+$recordsOnPage;
if ( $data[$module]['remaining'] < 1 ) {
    // This should take care of re-aliginng the offset with the actual number of records handled
    unset($data[$module]);
    //echo("Copy: for module $module<br>");
    score::sqlCopyScores($module,$bean);
}

echo("<script type='text/javascript'>document.location='index.php?module=Score&action=ProcessRescore&offset=".($_REQUEST['offset']+$recordsOnPage)."&total=".$_REQUEST['total']."&to_pdf=1&scoreThis=".$json->encode($data)."';</script>");
// echo("<a href='index.php?module=Score&action=ProcessRescore&offset=".($_REQUEST['offset']+$recordsOnPage)."&total=".$_REQUEST['total']."&to_pdf=1&scoreThis=".$json->encode($data)."'>Next page</a>");
