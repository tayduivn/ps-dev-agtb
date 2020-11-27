<?php

require_once('custom/install/CustomBPMProjectImporter.php');

// import workflow
$bpmFilename = 'custom/install/bpm/Calculation_Cluster_Match.php';
$importer = new CustomBPMProjectImporter();
$importer->importProject($bpmFilename);

// enable workflow
$workflow_id = getWorkflowId('Calculation Cluster Match');
if (!empty($workflow_id)) {
    $workflowBean = BeanFactory::getBean('pmse_Project', $workflow_id);
    if (!empty($workflowBean->id)) {
        $workflowBean->prj_status = 'ACTIVE';
        $workflowBean->save();
    }
}


function getWorkflowId($name)
{
    $bean = BeanFactory::newBean('pmse_Project');
    $sql = new SugarQuery();
    $sql->select('id');
    $sql->from($bean);
    $sql->Where()->equals('name', $name);
    $id = $sql->getOne();
    if (!empty($id)) {
        return $id;
    }
    return 0;
}
