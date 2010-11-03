<?php
$viewdefs ['Cases'] = 
array (
  'DetailView' => 
  array (
    'templateMeta' => 
    array (
      'form' => 
      array (
        'buttons' => 
        array (
          0 => 'EDIT',
          1 => 'DUPLICATE',
          2 => 'DELETE',
          3 => 'FIND_DUPLICATES',
          4 => 
          array (
            'customCode' => '<input title="{$MOD.LBL_CREATE_KB_DOCUMENT}" accessKey="M" class="button" onclick="this.form.return_module.value=\'Cases\'; this.form.return_action.value=\'DetailView\';this.form.action.value=\'EditView\';this.form.module.value=\'KBDocuments\'" type="submit" name="button" value="{$MOD.LBL_CREATE_KB_DOCUMENT}">',
          ),
        ),
      ),
      'maxColumns' => '2',
      'widths' => 
      array (
        0 => 
        array (
          'label' => '10',
          'field' => '30',
        ),
        1 => 
        array (
          'label' => '10',
          'field' => '30',
        ),
      ),
      'useTabs' => false,
    ),
    'panels' => 
    array (
      'default' => 
      array (
        0 => 
        array (
          0 => 
          array (
            'name' => 'case_number',
            'label' => 'LBL_CASE_NUMBER',
            'customCode' => '{if ($fields.onboard_customer_c.value == "1")}<div id="coloredfield" style="background-color:#DFF2BF; visibility: visible; witdh: 100%;">{$fields.case_number.value}</div>{else}{$fields.case_number.value}{/if}',
          ),
          1 => 
          array (
            'name' => 'team_name',
            'label' => 'LBL_TEAM',
          ),
        ),
        1 => 
        array (
          0 => 
          array (
            'name' => 'priority_level',
            'label' => 'LBL_PRIORITY_LEVEL',
          ),
          1 => 
          array (
            'name' => 'assigned_user_name',
            'label' => 'LBL_ASSIGNED_TO_NAME',
          ),
        ),
        2 => 
        array (
          0 => 
          array (
            'name' => 'status',
            'label' => 'LBL_STATUS',
          ),
          1 => 
          array (
            'name' => 'account_name',
            'label' => 'LBL_ACCOUNT_NAME',
          ),
        ),
        3 => 
        array (
          0 => 
          array (
            'name' => 'modified_by_name',
            'group' => 'modified_by_name',
            'customCode' => '{$fields.date_modified.value} {$APP.LBL_BY} {$fields.modified_by_name.value}&nbsp;',
            'label' => 'LBL_DATE_MODIFIED',
          ),
          1 => 
          array (
            'name' => 'support_service_level_c',
            'label' => 'Support_Service_Level_c_1',
          ),
        ),
        4 => 
        array (
          0 => 
          array (
            'name' => 'created_by_name',
            'group' => 'created_by_name',
            'customCode' => '{$fields.date_entered.value} {$APP.LBL_BY} {$fields.created_by_name.value}&nbsp;',
            'label' => 'LBL_DATE_ENTERED',
          ),
          1 => 
          array (
            'name' => 'submitter_c',
            'label' => 'Submitter_c',
          ),
        ),
        5 => 
        array (
          0 => 
          array (
            'name' => 'escalate_case',
            'label' => 'LBL_ESCALATE_CASE',
          ),
          1 => '',
        ),
        6 => 
        array (
          0 => 
          array (
            'name' => 'discuss_in_cometo_c',
            'label' => 'discuss_in_cometo_c',
          ),
          1 => 
          array (
            'name' => 'time_spent_c',
            'label' => 'LBL_TIME_SPENT',
          ),
        ),
        7 => 
        array (
          0 => 
          array (
            'name' => 'resolved_first_response_c',
            'label' => 'LBL_RESOLVED_FIRST_RESPONSE',
          ),
          1 => 
          array (
            'name' => 'request_type_c',
            'label' => 'Request_Type_c',
          ),
        ),
        8 => 
        array (
          0 => 
          array (
            'name' => 'product_category_c',
            'label' => 'Category__c',
          ),
          1 => 
          array (
            'name' => 'related_to_c',
            'label' => 'Related_To_c',
          ),
        ),
        9 => 
        array (
          0 => 
          array (
            'name' => 'document_resolution_c',
            'label' => 'Document_Resolution__c',
          ),
          1 => 
          array (
            'name' => 'presales_subcategory_c',
            'label' => 'presales_subcategory_c',
          ),
        ),
        10 => 
        array (
          0 => 
          array (
            'name' => 'doc_location_c',
            'label' => 'Documentation_Location_c',
          ),
          1 => 
          array (
            'name' => 'web_server_c',
            'label' => 'Web_Server__c',
          ),
        ),
        11 => 
        array (
          0 => 
          array (
            'name' => 'sugar_edition_c',
            'label' => 'Sugar_Edition_c',
          ),
          1 => 
          array (
            'name' => 'operating_system_c',
            'label' => 'Operating_System__c',
          ),
        ),
        12 => 
        array (
          0 => 
          array (
            'name' => 'sugar_version_c',
            'label' => 'Sugar_Version__c',
          ),
          1 => 
          array (
            'name' => 'php_version_c',
            'label' => 'PHP_Version__c',
          ),
        ),
        13 => 
        array (
          0 => 
          array (
            'name' => 'deployment_c',
            'label' => 'Deployment_Option__c',
          ),
          1 => 
          array (
            'name' => 'db_version_c',
            'label' => 'Database_Version__c',
          ),
        ),
        14 => 
        array (
          0 => 
          array (
            'name' => 'case_source_c',
            'label' => 'LBL_CASE_SOURCE',
          ),
          1 => 
          array (
            'name' => 'closed_date_time_c',
            'label' => 'LBL_CLOSED_DATE_TIME',
          ),
        ),
        15 => 
        array (
          0 => 
          array (
            'name' => 'temperature_c',
            'studio' => 'visible',
            'label' => 'LBL_TEMPERATURE_C',
          ),
          1 => 
          array (
            'name' => 'resolution_time_c',
            'label' => 'LBL_RESOLUTION_TIME',
          ),
        ),
        16 => 
        array (
          0 => 
          array (
            'name' => 'exclude_from_stats_c',
            'label' => 'LBL_EXCLUDE_FROM_STATS',
          ),
          1 => '',
        ),
        17 => 
        array (
          0 => 
          array (
            'name' => 'name',
            'label' => 'LBL_SUBJECT',
          ),
        ),
        18 => 
        array (
          0 => 
          array (
            'name' => 'description',
            'label' => 'LBL_DESCRIPTION',
          ),
        ),
        19 => 
        array (
          0 => 
          array (
            'name' => 'status_summary_c',
            'label' => 'Status_Summary_c',
          ),
        ),
        20 => 
        array (
          0 => 
          array (
            'name' => 'resolution',
            'label' => 'LBL_RESOLUTION',
          ),
        ),
      ),
    ),
  ),
);
?>
