<?php
require_once('pardotData.abstract.php');
class pardotVisitorActivity extends pardotData {
    var $id;
    var $type;
    var $type_name;
    var $created_at;
	var $details;

	function getInteractionData($override_data = array()){
		static $fieldMappings = array(
			'id' => 'visitor_activity_id',
			'type_name' => array('name', 'type'),
			'created_at' => 'date_modified',
		);
		
		$output = array();
		foreach($fieldMappings as $from => $to){
			if(isset($this->$from)){
				if(is_array($to)){
					foreach($to as $to_element){
						$output[$to_element] = $this->$from;
					}
				}
				else{
					if($to){
						$output[$to] = $this->$from;
					}
					else{
						$output[$from] = $this->$from;
					}
				}
			}
		}
		
		if(!empty($this->details)){
			$output['name'] = $this->details;
		}
		$output['modified_user_id'] = '1';
		$output['created_by'] = '1';
		$output['team_id'] = '1';
		$output['team_set_id'] = '1';
		
		if(!empty($override_data)){
			foreach($override_data as $key => $value){
				$output[$key] = $value;
			}
		}
		
		return $output;
	}
}
