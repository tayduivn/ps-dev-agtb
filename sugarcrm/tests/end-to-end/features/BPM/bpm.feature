# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@bpm @job1 @pr
Feature: Importing files related to Business Process Management

  Background:
    Given I am logged in


  @bpm_import_processDefinition
  Scenario Outline: Business Process > Import
    When I choose pmse_Project in modules menu and select "Import Process Definitions" menu item

    # Import bpm file and assign record identifier to the imported record
    When I create a new record *BP_1 by importing All Modules from "multi-import-all.bpm" file on #pmse_ProjectRecord.ImportBpmView

    # Verify and close alert
    When I verify and close alert
      | type    | message   |
      | Success | <message> |

    # Verify that Business Process module list view displays imported record
    Then I verify number of records in #pmse_ProjectList.ListView is 1

    # Enable process
    When I click on Enable button for *BP_1 in #pmse_ProjectList.ListView
    When I Confirm confirmation alert

    # Disable process
    When I click on Disable button for *BP_1 in #pmse_ProjectList.ListView
    When I Confirm confirmation alert

    # Delete Process
    And I delete *BP_1 record in pmse_Project list view

    # Verify Business Processes module list view contains no records
    Then I verify number of records in #pmse_ProjectList.ListView is 0

    # Verify Email Templates module list view contains 2 records
    When I choose pmse_Emails_Templates in modules menu
    Then I verify number of records in #pmse_Emails_TemplatesList.ListView is 2

    # Delete Imported Email Templates
    When I filter for the pmse_Emails_Templates record *PET_1 named "End Event Reached"
    And I delete *PET_1 record in pmse_Emails_Templates list view

    # Delete Imported Email Templates
    When I filter for the pmse_Emails_Templates record *PET_2 named "Notice for Description Field update"
    And I delete *PET_2 record in pmse_Emails_Templates list view

    # Verify Email Templates module list view contains no records
    Then I verify number of records in #pmse_Emails_TemplatesList.ListView is 0

    # Verify Business Rules module list view contains 1 record
    When I choose pmse_Business_Rules in modules menu
    Then I verify number of records in #pmse_Business_RulesList.ListView is 1

    # Delete Imported Business Rules
    When I filter for the pmse_Business_Rules record *BPR_1 named "US State"
    And I delete *BPR_1 record in pmse_Business_Rules list view

    # Verify Business Rules module list view contains no records
    Then I verify number of records in #pmse_Business_RulesList.ListView is 0

    Examples:
      | message                                                                |
      | Success Process Definitions was successfully imported into the system. |


  @bpm_import_emailTemplate
  Scenario: Process Email Templates > Import
    When I choose pmse_Emails_Templates in modules menu and select "Import Process Email Templates" menu item

    # Import pet file and assign record identifier to the imported record
    When I create a new record *PET_1 by importing "Lead_Assignment_Notification.pet" file on #pmse_Emails_TemplatesRecord.ImportBpmView

    # In-line edit created record
    When I click on Edit button for *PET_1 in #pmse_Emails_TemplatesList.ListView
    When I set values for *PET_1 in #pmse_Emails_TemplatesList.ListView
      | fieldName | value             |
      | name      | Lead Notification |

    # Save
    When I click on Save button for *PET_1 in #pmse_Emails_TemplatesList.ListView
    And I close alert

    # Verify process name is changed
    Then I verify fields for *PET_1 in #pmse_Emails_TemplatesList.ListView
      | fieldName | value             |
      | name      | Lead Notification |

    # Delete Email Template record
    When I click on Delete button for *PET_1 in #pmse_Emails_TemplatesList.ListView
    When I Confirm confirmation alert
    When I close alert


  @bpm_import_businessRules
  Scenario: Process Business Rules > Import
    When I choose pmse_Business_Rules in modules menu and select "Import Process Business Rules" menu item

    # Import pbr file
    When I create a new record *PBR_1 by importing "Global_Territory_Lead_Routing.pbr" file on #pmse_Business_RulesRecord.ImportBpmView

    # In-line edit created record
    When I click on Edit button for *PBR_1 in #pmse_Business_RulesList.ListView
    When I set values for *PBR_1 in #pmse_Business_RulesList.ListView
      | fieldName | value            |
      | name      | Global Territory |

    # Save
    When I click on Save button for *PBR_1 in #pmse_Business_RulesList.ListView
    And I close alert

    # Verify process name is changed
    Then I verify fields for *PBR_1 in #pmse_Business_RulesList.ListView
      | fieldName | value            |
      | name      | Global Territory |

    # Delete Business Rule
    When I click on Delete button for *PBR_1 in #pmse_Business_RulesList.ListView
    When I Confirm confirmation alert
    When I close alert


