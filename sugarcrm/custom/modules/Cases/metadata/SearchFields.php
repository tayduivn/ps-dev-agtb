<?php
$searchFields['Cases']['support_service_level_c'] = array('query_type'=>'default', 'options' => 'Support Service Level', 'template_var' => 'OPTIONS_SUPPORT_SERVICE_LEVEL', 'db_field' => array('cases_cstm.Support_Service_Level_c'));

$searchFields['Cases']['priority_level'] = array('query_type'=>'default', 'options' => 'Support Priority Levels', 'template_var' => 'OPTIONS_PRIORITY_LEVEL', 'db_field' => array('cases_cstm.priority_level'));
// added the keywords to the list of valid search fields
// jwhitcraft 3.12.10
$searchFields['Cases']['keyword'] = array( 'query_type'=>'default', 'operator' => 'keyword', 'keyword_index' => 'idx_cases_ft');
// end jwhitcraft
?>
