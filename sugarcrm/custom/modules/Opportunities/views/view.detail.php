<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once('include/MVC/View/views/view.detail.php');

class OpportunitiesViewDetail extends ViewDetail {
        function OpportunitiesViewDetail(){
                parent::ViewDetail();
        }
        function preDisplay(){
				// BEGIN sadek - NEED TO SPLIT SOME LANGUAGE OPTIONS INTO SEPARATE FILES FOR PERFORMANCE
				$GLOBALS['app_list_strings']['bp_options'] = IBMHelper::getLargeEnum('bp_options');
				// END sadek - NEED TO SPLIT SOME LANGUAGE OPTIONS INTO SEPARATE FILES FOR PERFORMANCE
                parent::preDisplay();
        }
}
