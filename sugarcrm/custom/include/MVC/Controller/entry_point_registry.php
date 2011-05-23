<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

// START jvink - need an unauthenticated entry point to get subpanel data
// for ibm_RelatedContent
$entry_point_registry['ibm_RelatedContent_SubpanelData'] = array('file' => 'modules/ibm_RelatedContent/ibm_RelatedContent_SubpanelData.php', 'auth' => false);
// END jvink

?>