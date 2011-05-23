<?php
// created: 2011-02-24 14:47:10
$layout_defs["Opportunities"]["subpanel_setup"]["opportunities_ibm_relatedcontent"] = array (
	'order' => 43,
	'module' => 'ibm_RelatedContent',
	'subpanel_name' => 'default',
	'sort_order' => 'asc',
	'sort_by' => 'id',
	'refresh_page' => 0,
	'title_key' => 'LBL_OPPORTUNITIES_IBM_RELATEDCONTENT_FROM_IBM_RELATEDCONTENT_TITLE',
	
	'top_buttons' => array(
		array('widget_class' => 'SubPanelTopDropdownRelatedContent'),
	),
	
	'get_subpanel_data' => 'opportunities_ibm_relatedcontent',
	'external_source' => array(
		// use an xml file as input
		'type' => 'xml',
		// where to grab the url (TODO --> move this to config.php !)
		//'url' => 'http://localhost/ibmpilot/index.php?entryPoint=ibm_RelatedContent_SubpanelData',
		'url' => $GLOBALS['sugar_config']['site_url'] . '/index.php?entryPoint=ibm_RelatedContent_SubpanelData',
		// parameters to add to our url (source param -> destination param)
		'url_params' => array (
			'record' => 'opportunity_id',
			'related_content_filter' => 'level20',
		),
		// xpath to specify the tags containing each record
		'record_base' => '/content/row', 	
		// map xml fields with xpath to bean fields
		'field_map' => array(
			'name' => array(
				'xpath' => './internal_id',
			),
			'title' => array(
				'xpath' => './title',
			),
			'file_type' => array(
				'xpath' => './file_type',
			),
			'products' => array(
				'xpath' => './products',
			),
			'industries' => array(
				'xpath' => './industries',
			),
			'publish_date' => array(
				'xpath' => './publish_date',
			),
			'info_author_first' => array(
				'xpath' => './info_author_first',
			),
			'info_author_last' => array(
				'xpath' => './info_author_last',
			),
			'info_author_email' => array(
				'xpath' => './info_author_email',
			),
			'info_abstract' => array(
				'xpath' => './info_abstract',
			),
		),	
	),
);
