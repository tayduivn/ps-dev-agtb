<?php
$viewdefs ['Cases'] = 
array (
  'EditView' => 
  array (
    'templateMeta' => 
    array (
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
            'type' => 'readonly',
            'displayParams' => 
            array (
              'required' => true,
            ),
            'label' => 'LBL_NUMBER',
          ),
          1 => 
          array (
            'name' => 'team_name',
            'displayParams' => 
            array (
              'required' => true,
            ),
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
            'customCode' => '
<select name="{$fields.status.name}" id="{$fields.status.name}" title=\'\' tabindex="0" OnChange=\'checkCaseStatusDependentDropdown()\' >
{if isset($fields.status.value) && $fields.status.value != \'\'}
{html_options options=$fields.status.options selected=$fields.status.value}
{else}
{html_options options=$fields.status.options selected=$fields.status.default}
{/if}
</select>
<script src=\'custom/include/javascript/custom_javascript.js\'></script>
',
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
            'name' => 'escalate_case',
            'label' => 'LBL_ESCALATE_CASE',
          ),
          1 => 
          array (
            'name' => 'submitter_c',
            'label' => 'Submitter_c',
          ),
        ),
        4 => 
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
        5 => 
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
        6 => 
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
            'customCode' => '
<select name="{$fields.related_to_c.name}" id="{$fields.related_to_c.name}" title=\'\' tabindex="0" OnChange=\'checkCaseStatusDependentDropdown()\' >
{if isset($fields.related_to_c.value) && $fields.related_to_c.value != \'\'}
{html_options options=$fields.related_to_c.options selected=$fields.related_to_c.value}
{else}
{html_options options=$fields.related_to_c.options selected=$fields.related_to_c.default}
{/if}
</select>
',
          ),
        ),
        7 => 
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
        8 => 
        array (
          0 => 
          array (
            'name' => 'doc_location_c',
            'label' => 'Documentation_Location_c',
          ),
          1 => '',
        ),
        9 => 
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
        10 => 
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
        11 => 
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
        12 => 
        array (
          0 => 
          array (
            'name' => 'case_source_c',
            'label' => 'LBL_CASE_SOURCE',
          ),
          1 => 
          array (
            'name' => 'web_server_c',
            'label' => 'Web_Server__c',
          ),
        ),
        13 => 
        array (
          0 => 
          array (
            'name' => 'temperature_c',
            'studio' => 'visible',
            'label' => 'LBL_TEMPERATURE_C',
          ),
          1 => 
          array (
            'name' => 'exclude_from_stats_c',
            'label' => 'LBL_EXCLUDE_FROM_STATS',
          ),
        ),
        14 => 
        array (
          0 => 
          array (
            'name' => 'name',
            'displayParams' => 
            array (
              'size' => 75,
              'required' => true,
            ),
            'label' => 'LBL_SUBJECT',
          ),
        ),
        15 => 
        array (
          0 => 
          array (
            'name' => 'description',
            'displayParams' => 
            array (
              'rows' => '8',
              'cols' => '80',
            ),
            'nl2br' => true,
            'label' => 'LBL_DESCRIPTION',
          ),
        ),
        16 => 
        array (
          0 => 
          array (
            'name' => 'status_summary_c',
            'label' => 'Status_Summary_c',
            'customCode' => '<textarea id="{$fields.status_summary_c.name}" name="{$fields.status_summary_c.name}" rows="1" cols="80">{$fields.status_summary_c.value}</textarea>',
          ),
        ),
        17 => 
        array (
          0 => 
          array (
            'name' => 'resolution',
            'displayParams' => 
            array (
              'rows' => '5',
              'cols' => '80',
            ),
            'nl2br' => true,
            'label' => 'LBL_RESOLUTION',
          ),
        ),
      ),
    ),
  ),
);
?>
