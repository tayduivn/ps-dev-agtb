<?php

require_once('modules/pmse_Inbox/engine/PMSEProjectImporter.php');

// import workflow
$bpmFilename = 'custom/install/data/Reminder_on_proposition_answer.bpm';
// E-Mail Template ID
$options['selectedIds'] = array('915b36fc-25d7-11eb-ac45-0242ac120008');
$importer = new PMSEProjectImporter();
$importer->importProject($bpmFilename, $options);

// enable workflow
$workflow_id = getWorkflowId('Reminder on proposition answer');
if(!empty($workflow_id)){
    $workflowBean = BeanFactory::getBean('pmse_Project', $workflow_id);
    if(!empty($workflowBean->id)) {
        $workflowBean->prj_status = 'ACTIVE';
        $workflowBean->save();
    }
}


function getWorkflowId($name){
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
