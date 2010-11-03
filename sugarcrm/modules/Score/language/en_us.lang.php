<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
 * By installing or using this file, You have unconditionally agreed to the
 ********************************************************************************/
$mod_strings = array (
  'LBL_TEAM' => 'Team',
  'LBL_TEAM_ID' => 'Team Id',
  'LBL_ASSIGNED_TO_ID' => 'Assigned User Id',
  'LBL_ASSIGNED_TO_NAME' => 'Assigned to',
  'LBL_ID' => 'ID',
  'LBL_DATE_ENTERED' => 'Date Created',
  'LBL_DATE_MODIFIED' => 'Date Modified',
  'LBL_MODIFIED' => 'Modified By',
  'LBL_MODIFIED_ID' => 'Modified By Id',
  'LBL_MODIFIED_NAME' => 'Modified By Name',
  'LBL_CREATED' => 'Created By',
  'LBL_CREATED_ID' => 'Created By Id',
  'LBL_DESCRIPTION' => 'Description',
  'LBL_DELETED' => 'Deleted',
  'LBL_NAME' => 'Name',
  'LBL_SAVING'=>'Saving...',
  'LBL_SAVED'=>'Saved',
  'LBL_CREATED_USER' => 'Created by User',
  'LBL_MODIFIED_USER' => 'Modified by User',
  'LBL_LIST_FORM_TITLE' => 'Score List',
  'LBL_MODULE_NAME' => 'Score',
  'LBL_MODULE_TITLE' => 'Score',
  // Menu text
  'LNK_CAMPAIGN_RESCORE' => 'Manage Campaign Scoring',
  'LNK_CHANGE_SCORES' => 'Manage Record Scoring',
  'LNK_RECALC_SCORES' => 'Score Records Manually',

  // Rescore text
  'LBL_RESCORE_SUBMIT' => 'Score Selected',
  'LBL_RESCORE_MODULE' => 'Module',
  'LBL_RESCORE_UNSCORED' => 'Unscored',
  'LBL_RESCORE_DIRTY' => 'Needs Rescore',
  'LBL_RESCORE_TOTAL' => 'Total Records Available',
  'LBL_MANUAL_RESCORE' => 'Manually Score Records',
  'LBL_RESCORE_STARTING' => 'Starting to rescore records, please be patient.',
  'LBL_RESCORE_DONE' => 'Finished rescoring records.',
  'LBL_RESCORE_HELP_TOTAL' => 'Select the records for which to recalculate scores. Scores for some records may no longer be current due to either new or changed scoring rules or new record relationships.',

  // Admin text
  'LBL_ADMIN_SETTINGS' => 'Record Scoring',
  'LBL_ADD_NEW_RULE' => 'Select a field for a new scoring rule',
  'LBL_ADD_NEW_NONE' => 'None',
  'LBL_COL_WEIGHT' => 'Weight',
  'LBL_COL_SCORE' => 'Score',
  'LBL_COL_CALC_SCORE' => 'Weighted Score',
  'LBL_COL_MUL' => 'Boost Factor',
  'LBL_COL_ENABLED' => 'Enable / Delete',
  'LBL_ADD_VALUE' => 'Add',
  'LBL_ENABLE_SCORE_FOR' => 'Enable scoring for ',
  'LBL_APPLY_MULT_TO' => 'Apply boost factors for this module to',
  'LBL_APPLY_CURRENT_RECORD' => 'Current Record',
  'LBL_APPLY_PARENT' => 'Parent Record',
  'LBL_DELETE_ROW' => 'Are you sure you wish to delete the selected value from this rule?',
  'LBL_DELETE_RULE' => 'Are you sure you want to remove the current rule completely?',
  // Admin Help Text
  'LBL_HELP_NEW_RULE' => 'Scoring rules can be based upon a record&apos;s field values or upon its relationships.',
  'LBL_HELP_BOOST_APPLY' => 'Select which module’s record scores will be multiplied by the Boost Factors. You can select either the module contributing the score points or the related module.',
  'LBL_HELP_WEIGHT' => 'Provide a Weight for the field so that the record score is affected by the field value more or less than other field values, depending on relative importance.',
  'LBL_HELP_COL_ENABLE' => 'Check the Checkbox to enable contributions to the record score by the rule.  Click the Delete icon to remove the rule.',
  'LBL_HELP_COL_WEIGHTED' => 'The Weighted Score is the total score contributed by the field value as affected by the weight specified for the field.  Field Value Score + Weight = Weighted Score.',
  'LBL_HELP_COL_BOOST' => 'Provide a Boost Factor for specific field values in order to increase or decrease the designated record score by a multiplied amount. The boost factor percentage reflects the percentage increase or decrease.',

  'LBL_HELP_COL_DATE_VALUE' => 'Enter the minimum date for which the rule applies.',

  // Score Board Text
  'LBL_SB_TITLE' => 'Score Details',
  'LBL_SB_TH_FIELD' => 'Field',
  'LBL_SB_TH_VALUE' => 'Data',
  'LBL_SB_TH_SCORE' => 'Score',
  'LBL_SB_TH_MUL' => 'Boost',
  'LBL_SB_INVALIDRULE' => 'INVALID',
  'LBL_SB_MULT_PARENT' => ' ( Boost Added To Parent ) ',
  'LBL_SB_TOTAL' => 'Total',

  // Campaign Rescore Text
  'LBL_CS_TITLE' => 'Campaigns Scoring',
  'LBL_CS_LIST_TITLE' => 'Campaigns',
  'LBL_CS_TH_NAME' => 'Campaign Name',
  'LBL_CS_TH_SCORE' => 'Score',
  'LBL_CS_TH_MUL' => 'Boost Factor',


  // Dropdown Rule Text
  'LBL_ADD_DropdownRule' => 'Dropdown Field Value',
  'LBL_DROPDOWN_ADD' => 'Select a field to score',
  'LBL_DROPDOWNRULE_TITLE' => 'Scoring for field',
  'LBL_DROPDOWNRULE_FIELDVALUE' => 'Field Value',
  'LBL_DROPDOWNRULE_DEFAULT' => 'Default',

  // DateField Rule Text
  'LBL_ADD_DateFieldRule' => 'Date/Time Field Value',
  'LBL_DATEFIELD_ADD' => 'Select a date/time field to score',
  'LBL_DATEFIELDRULE_TITLE' => 'Scoring for field',
  'LBL_DATEFIELDRULE_FIELDVALUE' => 'Days Elapsed',
  
  // Checkbox Rule Text
  'LBL_ADD_CheckboxRule' => 'Checkbox Status',
  'LBL_CHECKBOX_ADD' => 'Select a checkbox to score',
  'LBL_CHECKBOXRULE_TITLE' => 'Scoring for checkbox',
  'LBL_CHECKBOXRULE_FIELDVALUE' => 'Checkbox status',
  'LBL_CHECKBOXRULE_CHECKED' => 'Checked',
  'LBL_CHECKBOXRULE_UNCHECKED' => 'Unchecked',

  // Text Rule Text
  'LBL_TEXT_ADD' => 'Select a text field to score',
  'LBL_TEXTRULE_TITLE' => 'Scoring for text',
  'LBL_TEXTRULE_FIELDVALUE' => 'Field text',
  'LBL_TEXTRULE_DEFAULT' => 'Default',

  // Number Rule Text
  'LBL_NUMBER_ADD' => 'Select a numeric field to score',
  'LBL_NUMBERRULE_TITLE' => 'Scoring for numeric field',
  'LBL_NUMBERRULE_FIELDMIN' => 'Min Value',
  'LBL_NUMBERRULE_FIELDMAX' => 'Max Value',
  'LBL_NUMBERRULE_DEFAULT' => 'Default',

  // Campaign Rule Text
  'LBL_CampaignRule_LABEL' => 'Campaign Score',
  'LBL_ADD_CampaignRule' => 'Import campaign score',
  'LBL_CAMPAIGNRULE_TITLE' => 'Enable importing the score from the related campaign',
  'LBL_CAMPAIGNRULE_LINK' => 'Change all campaign score values',

  // Related Rule Text
  'LBL_RelatedRule_LABEL' => 'Number of related records',
  'LBL_ADD_RelatedRule' => 'Number of related records',
  'LBL_RELATEDRULE_TITLE' => 'Score based on the number of related records',
  'LBL_RELATEDRULE_VALUE' => 'Number of related records',
  'LBL_RELATEDRULE_FIELDMAX' => 'Maximum',
  'LBL_HELP_COL_RELATE_VALUE' => 'Enter the number of records for which the rule applies.',
  'LBL_RELATEDRULE_DEFAULT' => 'Default',
  'LBL_RELATEDRULE_DEFAULT_HELP' => 'Default scoring is applied when the number of related records is greater than the highest number and less than the lowest number entered in the scoring rule.',
);