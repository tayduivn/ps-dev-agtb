<?php
/**
 * @author dymovsky
 */
require_once 'modules/Reports/Report.php';

class SubpanelFromReports extends Report {
	public function __construct($report) {
		parent::Report($report->content);
		if (isset($this->report_def['display_columns'])) {	
			if (!empty($this->report_def['display_columns'])) {
				foreach ($this->report_def['display_columns'] as $key => $column) {
					// If self column exists, return Report class
					if ($column['table_key'] == 'self') {
						return $this;
					}
				}
			} 
			$this->_appendNecessaryColumn();
		}
	} 
	
	/**
	 *  Because one self column needed to generate primaryid for subpanel list
	 */
	private function _appendNecessaryColumn() {
		array_push($this->report_def['display_columns'], array (
			'label' => 'Name',
			'name' => 'name',
			'table_key' => 'self'
		));
	}
}