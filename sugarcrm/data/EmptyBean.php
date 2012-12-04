<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
*/

/**
 * Empty bean - to perform non-static functions on bean without loading specific beans
 */
class EmptyBean extends SugarBean
{
    // this bean has no vardefs
    public $disable_vardefs = true;
    // no custom fields
    public $disable_custom_fields = true;
}
