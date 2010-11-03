<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once('include/MVC/View/views/view.edit.php');

class ITRequestsViewEdit extends ViewEdit {
    function ITRequestsViewEdit() {
        parent::ViewEdit();
    }

    function display() {
        global $current_user;

        $this->ev->ss->assign("assignmessage", "<div style='border: 1px solid #4F8A10; background-color: #DFF2BF; padding: 5px 5px;'><h2>ITRequests Help:</h2>\n".
                "Need help with the ITRequest module? <a href='http://internalwiki.sjc.sugarcrm.pvt/index.php/Creating_ITRequests' target='_blank'>Click Here</a><br /></div><br />\n");

        $this->ev->ss->assign("NEWITR", 'false');

        if(is_null($this->bean->id)) {
            $this->bean->assigned_user_id = null;

            $this->ev->ss->assign("NEWITR", 'true');
        }

        if(!empty($this->bean->resolution)) {
            $resolution = $this->bean->resolution;
            if(preg_match_all("/itrequest[s]?\s[#]?([0-9]+)/i", $resolution, $pregmatches, PREG_OFFSET_CAPTURE)){
                for($idx = 0; $idx < count($pregmatches[0]); $idx++){
                    $itres = $GLOBALS['db']->query("select id from itrequests where itrequest_number = '{$pregmatches[1][$idx][0]}' and deleted='0'");
                    if($itres){
                        $row = $GLOBALS['db']->fetchByAssoc($itres);
                        $itr_id = $row['id'];
                        $resolution = str_replace($pregmatches[0][$idx][0], "<a href=\"index.php?module=ITRequests&action=DetailView&record={$itr_id}\">{$pregmatches[0][$idx][0]}</a>", $resolution);
                    }
                }
            }
            if(preg_match_all("/bug[s]?\s[#]?([0-9]+)/i", $resolution, $pregmatches, PREG_OFFSET_CAPTURE)){
                for($idx = 0; $idx < count($pregmatches[0]); $idx++){
                    $bugres = $GLOBALS['db']->query("select id from bugs where bug_number = '{$pregmatches[1][$idx][0]}' and deleted='0'");
                    if($bugres){
                        $row = $GLOBALS['db']->fetchByAssoc($bugres);
                        $bug_id = $row['id'];
                        $resolution = str_replace($pregmatches[0][$idx][0], "<a href=\"index.php?module=Bugs&action=DetailView&record={$bug_id}\">{$pregmatches[0][$idx][0]}</a>", $resolution);
                    }
                }
            }
            $resolution = url2html($resolution);

            $this->bean->resolution = html_entity_decode($resolution);
        }

        $js = "\n<script>\n";

        if (!$current_user->check_role_membership('IT')) {
            $js .= "document.getElementById('escalation_c').disabled = true;\n";
            $js .= "document.getElementById('target_date').disabled = true;\n";
            $js .= "document.getElementById('development_time').disabled = true;\n";
            $js .= "document.getElementById('assigned_user_name').disabled = true;\n";
            $js .= "document.getElementById('btn_assigned_user_name').disabled = true;\n";
            $js .= "document.getElementById('btn_clr_assigned_user_name').disabled = true;\n";
        }

        $js .= "\n</script>\n";
        parent::display();
        echo $js;
    }
}
