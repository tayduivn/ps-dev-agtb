<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/**
 * Class for separate storage of Email texts
 */
class EmailText extends SugarBean
{
	var $disable_row_level_security = true;
    var $table_name = 'emails_text';
    var $module_name = "EmailText";
    var $module_dir = 'EmailText';
    var $object_name = 'EmailText';
    var $disable_custom_fields = true;
}