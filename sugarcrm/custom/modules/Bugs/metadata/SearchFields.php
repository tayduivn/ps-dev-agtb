<?php
// added the keyword field to the SearchFields
// jwhitcraft 3.12.10
$searchFields['Bugs']['keyword'] =  array( 'query_type'=>'default', 'operator' => 'keyword', 'keyword_index' => 'bugs_ft');
// end jwhitcraft
$searchFields['Bugs']['fixed_in_release'] = array('query_type'=>'default','options' => 'bug_release_dom', 'template_var' => 'FIXED_IN_RELEASE_OPTIONS', 'options_add_blank' => true, 'operator' => '=',);
$searchFields['Bugs']['product_category'] = array('query_type'=>'default','options' => 'product_category_dom', 'template_var' => 'PRODUCT_CATEGORY_OPTIONS', 'options_add_blank' => false, 'operator' => '=',);

?>
