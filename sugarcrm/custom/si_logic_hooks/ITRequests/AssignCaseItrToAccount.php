<?php

class assignCaseItrToAccount {

    public function assignITR(&$bean, $event, $arguments) {

        // if the even is not post save then we return false;
        $this->log('Event Type: ' . $event);
        if($event != "after_save") return false;

        // we need to test the post to make sure that the return_module is Cases
        if($_REQUEST['relate_to'] == "itrequests_cases" && empty($_REQUEST['relate_id']) === false) {
            $this->log('Has Parent Type of Case with ID of ' .$_REQUEST['relate_id']);
            // load the case so we can get the account id
            $case = new aCase();
            $case->retrieve($_REQUEST['relate_id']);

            // load the ITR accounts relationship
            $bean->load_relationship('accounts');
            // add the account from the Case bean to the ITR Bean
            $this->log('Adding Case to ITR Bean' . $case->account_id);
            $bean->accounts->add($case->account_id);

            // clean up the variables
            unset($case);

            return true;
        }

        $this->log('No Case Found');

        // parent type is not cases or the parent_id is empty
        return false;
    }

    public function log($msg)
    {
        syslog(LOG_DEBUG, date('r') . 'CaseAccountITR - ' . $msg);
    }
}