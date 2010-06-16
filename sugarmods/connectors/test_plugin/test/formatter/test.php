<?php
class ext_rest_test_formatter extends default_formatter {

public function getDetailViewFormat() { 
   $mapping = $this->getSourceMapping();
   $mapping_name = !empty($mapping['beans'][$this->_module]['website']) ? $mapping['beans'][$this->_module]['website'] : '';
   
   if(!empty($mapping_name)) {
      $this->_ss->assign('mapping_name', $mapping_name);
      return $this->fetchSmarty();
   }

   $GLOBALS['log']->error($GLOBALS['app_strings']['ERR_MISSING_MAPPING_ENTRY_FORM_MODULE']);
   return '';
}	
	
public function getIconFilePath() {
   return 'custom/modules/Connectors/connectors/formatters/ext/rest/test/tpls/test.jpg';
}   

}
?>