<?php

// START jvink customizations
// custom controller to implement duplicates from ListView
class OpportunitiesController extends SugarController {
	
    public function action_dupFromListView() {
        if ( !empty($_REQUEST['uid']) ) {
            $recordId = $_REQUEST['uid'];
			$bean = SugarModule::get($_REQUEST['module'])->loadBean();
			$bean->retrieve($recordId);
			$GLOBALS['log']->debug('XXX custom controller -> '.$recordId);
			
			// go to editview to duplicate the passed id
			SugarApplication::redirect('index.php?module=Opportunities&action=EditView&isDuplicate=true&return_id='.$recordId.'&record='.$recordId.'&return_action=index&duplicateId='.$recordId.'&return_module=Opportunities');
        }
    }
}
// END jvink customizations

?>