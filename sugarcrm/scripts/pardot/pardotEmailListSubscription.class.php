<?php
require_once('pardotData.abstract.php');
class pardotEmailListSubscription extends pardotData {
    var $id;
    var $list_id;
    var $prospect_id;
    var $did_opt_in;
    var $did_opt_out;
    var $created_at;
    var $updated_at;
}