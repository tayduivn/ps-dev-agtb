# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@Opportunities @job4 @ent-only
Feature: Renewal Opp > Auto-generate Renewal Opportunity when original renewable opportunity is closed as 'Closed Won'

  Background:
    Given I am logged in

  @renewal_opportunity
  Scenario: Renewal Opportunity > Auto-generate Renewal Opportunity when original renewable service opp is closed as 'Closed Won'
    Given Accounts records exist:
      | *   | name      |
      | A_1 | Account_1 |
    And Opportunities records exist related via opportunities link to *A_1:
      | *     | name  |
      | Opp_1 | Opp_1 |
    And RevenueLineItems records exist related via revenuelineitems link to *Opp_1:
      | *name | date_closed | worst_case | likely_case | best_case | sales_stage         | quantity | service | service_start_date | service_duration_value | service_duration_unit | renewable |
      | RLI_1 | now         | 1000       | 2000        | 3000      | Prospecting         | 1        | true    | 2020-01-01         | 2                      | year                  | true      |
      | RLI_2 | now         | 1000       | 2000        | 3000      | Closed Lost         | 1        | true    | 2020-01-02         | 2                      | year                  | false     |
      | RLI_3 | now         | 1000       | 2000        | 3000      | Needs Analysis      | 1        | true    | 2020-01-03         | 2                      | year                  | true      |
      | RLI_4 | now         | 1000       | 2000        | 3000      | Perception Analysis | 1        | false   |                    |                        |                       |           |

    # Change Sales Stage of RLIs to "Close Won"
    When I perform mass update of RevenueLineItems [*RLI_1, *RLI_3, *RLI_4] with the following values:
      | fieldName   | value      |
      | sales_stage | Closed Won |

    # Verify that new renewal RLIs are auto-generated
    Then I verify number of records in #RevenueLineItemsList.ListView is 6

    # Since renewal opportunity has the same name as original, rename original opportunity to differentiate original and renewal
    When I edit *Opp_1 record in Opportunities list view with the following values:
      | fieldName | value          |
      | name      | Original_Opp_1 |

    # Assign ID for newly generated renewal opportunity
    When I filter for the Opportunities record *OppRenewal named "Opp_1"

    # Verify that Renewal Opportunity is generated properly
    When I select *OppRenewal in #OpportunitiesList.ListView
    When I click show more button on #OppRenewalRecord view
    Then I verify fields on #OppRenewalRecord.HeaderView
      | fieldName | value                 |
      | name      | Opp_1                 |
      | renewal   | January, 2022 Renewal |
    Then I verify fields on #OppRenewalRecord.RecordView
      | fieldName           | value          |
      | date_closed         | 01/03/2022     |
      | sales_status        | In Progress    |
      | amount              | $4,000.00      |
      | renewal_parent_name | Original_Opp_1 |

    # Add another opportunity
    Given Opportunities records exist related via opportunities link to *A_1:
      | *name |
      | Opp_2 |
    And RevenueLineItems records exist related via revenuelineitems link to *Opp_2:
      | *name | date_closed | worst_case | likely_case | best_case | sales_stage | quantity | service | service_start_date | service_duration_value | service_duration_unit | renewable |
      | RLI_5 | now         | 1400       | 1500        | 1600      | Prospecting | 1        | true    | 2020-02-05         | 2                      | year                  | true      |

    # Link newly created opportunity to the original opportunity
    When I choose Opportunities in modules menu
    When I select *Opp_2 in #OpportunitiesList.ListView
    When I click show more button on #Opp_2Record view
    When I click Edit button on #Opp_2Record header
    When I provide input for #Opp_2Record.RecordView view
      | renewal_parent_name |
      | Original_Opp_1      |
    When I click Save button on #Opp_2Record header
    When I close alert

    # Close newly created opportunity by closing linked RLI record
    When I edit *RLI_5 record in RevenueLineItems list view with the following values:
      | fieldName   | value      |
      | sales_stage | Closed Won |

    When I choose Opportunities in modules menu

    # Verify number of opportunities records
    Then I verify number of records in #OpportunitiesList.ListView is 3

    # Verify that Renewal Opportunity is updated correctly
    When I select *OppRenewal in #OpportunitiesList.ListView
    When I click show more button on #OppRenewalRecord view
    Then I verify fields on #OppRenewalRecord.HeaderView
      | fieldName | value                  |
      | name      | Opp_1                  |
      | renewal   | February, 2022 Renewal |
    Then I verify fields on #OppRenewalRecord.RecordView
      | fieldName           | value          |
      | date_closed         | 02/05/2022     |
      | sales_status        | In Progress    |
      | amount              | $5,500.00      |
      | renewal_parent_name | Original_Opp_1 |

    # Delete renewal opportunity -  this will also erase of all RLI records linked to renewal opportunity
    When I delete *OppRenewal record in Opportunities list view
    Then I should not see [*OppRenewal] on Opportunities list view


  @multiple_RLI_renewal_opportunity @SS-198
  Scenario: Opportunities >  Add in multiple RLI's with the same product catalog id's on a renewal opportunity
    # Create product records
    Given ProductTemplates records exist:
      | *name | discount_price | cost_price | list_price | service | service_start_date | service_duration_value | service_duration_unit | renewable |
      | Alex1 | 100            | 100        | 100        | true    | 2020-01-01         | 2                      | year                  | true      |

    # Create Account
    Given Accounts records exist:
      | *name |
      | Acc_1 |

    # Create an opportunity
    When I choose Opportunities in modules menu
    When I click Create button on #OpportunitiesList header
    When I provide input for #OpportunitiesDrawer.HeaderView view
      | *     | name          |
      | Opp_1 | Opportunity 1 |
    When I provide input for #OpportunitiesDrawer.RecordView view
      | *     | account_name |
      | Opp_1 | Acc_1        |

    # Provide input for the RLI
    When I provide input for #OpportunityDrawer.RLITable view for 1 row
      | date_closed | product_template_name | *name |
      | 12/12/2020  | Alex1                 | RLI1  |

    # Save new opportunity
    When I click Save button on #OpportunitiesDrawer header
    When I close alert

    # Edit RLI and change status
    When I choose RevenueLineItems in modules menu
    When I click on Edit button for *RLI1 in #RevenueLineItemsList.ListView
    When I set values for *RLI1 in #RevenueLineItemsList.ListView
      | fieldName   | value      |
      | sales_stage | Closed Won |
    When I click on Save button for *RLI1 in #RevenueLineItemsList.ListView
    When I close alert

    # Rename closed won opportunity
    When I choose Opportunities in modules menu
    When I click on Edit button for *Opp_1 in #OpportunitiesList.ListView
    When I set values for *Opp_1 in #OpportunitiesList.ListView
      | fieldName | value                |
      | name      | Opportunity Original |

    # Save renamed opportunity
    When I click on Save button for *Opp_1 in #OpportunitiesList.ListView
    When I close alert

    # Create an 2nd opportunity
    When I choose Opportunities in modules menu
    When I click Create button on #OpportunitiesList header
    When I click show more button on #OpportunitiesDrawer view
    When I provide input for #OpportunitiesDrawer.HeaderView view
      | *      | name          |
      | Opp_1U | Opportunity 2 |

    When I provide input for #OpportunitiesDrawer.RecordView view
      | *      | account_name | renewal_parent_name  |
      | Opp_1U | Acc_1        | Opportunity Original |

    # Provide input for the RLI
    When I provide input for #OpportunityDrawer.RLITable view for 1 row
      | date_closed | product_template_name | *name |
      | 12/12/2020  | Alex1                 | RLI2  |

    # Save 2nd opportunity
    When I click Save button on #OpportunitiesDrawer header
    When I close alert

    # Edit RLI and change status
    When I choose RevenueLineItems in modules menu
    When I click on Edit button for *RLI2 in #RevenueLineItemsList.ListView
    When I set values for *RLI2 in #RevenueLineItemsList.ListView
      | fieldName   | value      |
      | sales_stage | Closed Won |
    When I click on Save button for *RLI2 in #RevenueLineItemsList.ListView
    When I close alert

    # Verify Roll up of RLI's to parent opportunity
    # Assign ID for newly generated renewal opportunity
    When I filter for the Opportunities record *Opp_2 named "Opportunity 1"
    When I select *Opp_2 in #OpportunitiesList.ListView
    Then I verify fields on #Opp_2Record.RecordView
      | fieldName | value   |
      | best_case | $200.00 |
      | amount    | $200.00 |
    When I open the revenuelineitems subpanel on #Opp_2Record view
    Then I verify number of records in #Opp_2Record.SubpanelsLayout.subpanels.revenuelineitems is 2


  @renewal_opportunity @SS-318 @AT-348
  Scenario: Service Opportunity > Verify value of 'Service Start Date' field on opportunity record view
    Given Accounts records exist:
      | *   | name      |
      | A_1 | Account_1 |
    And Opportunities records exist related via opportunities link to *A_1:
      | *     | name  |
      | Opp_1 | Opp_1 |
    And RevenueLineItems records exist related via revenuelineitems link to *Opp_1:
      | *name | date_closed | worst_case | likely_case | best_case | sales_stage         | quantity | service | service_start_date | service_duration_value | service_duration_unit | renewable |
      | RLI_1 | now         | 1000       | 2000        | 3000      | Prospecting         | 1        | true    | 2020-01-05         | 2                      | year                  | true      |
      | RLI_2 | now         | 1000       | 2000        | 3000      | Closed Lost         | 1        | true    | 2020-01-02         | 2                      | year                  | false     |
      | RLI_3 | now         | 1000       | 2000        | 3000      | Needs Analysis      | 1        | true    | 2020-01-03         | 2                      | year                  | true      |
      | RLI_4 | now         | 1000       | 2000        | 3000      | Perception Analysis | 1        | false   |                    |                        |                       |           |
      | RLI_5 | now         | 1000       | 2000        | 3000      | Closed Won          | 1        | true    | 2020-01-01         | 3                      | month                 | true      |

    # Navigate to opportunity record view
    When I choose Opportunities in modules menu
    When I select *Opp_1 in #OpportunitiesList.ListView

    # Verify value of service_start_date field
    Then I verify fields on #Opp_1Record.RecordView
      | fieldName          | value      |
      | service_start_date | 01/03/2020 |

    # Change Sales Stage of RLIs to "Close Won"
    When I perform mass update of RevenueLineItems [*RLI_1, *RLI_3, *RLI_4] with the following values:
      | fieldName   | value      |
      | sales_stage | Closed Won |

    # Navigate to opportunity record view
    When I choose Opportunities in modules menu
    When I select *Opp_1 in #OpportunitiesList.ListView

    # Verify value of service_start_date field
    Then I verify fields on #Opp_1Record.RecordView
      | fieldName          | value      |
      | service_start_date | 01/01/2020 |

    # Add new RLI record
    When I open the revenuelineitems subpanel on #Opp_1Record view
    When I create_new record from revenuelineitems subpanel on #Opp_1Record view
    When I click show more button on #RevenueLineItemsDrawer view
    When I provide input for #RevenueLineItemsDrawer.HeaderView view
      | *     | name  |
      | RLI_6 | RLI_6 |
    When I provide input for #RevenueLineItemsDrawer.RecordView view
      | *     | date_closed | likely_case | quantity | service | service_start_date |
      | RLI_6 | 12/31/2019  | 2000        | 1        | true    | 12/31/2019         |
    When I click Save button on #RevenueLineItemsDrawer header
    When I close alert

    # Verify value of service_start_date field
    Then I verify fields on #Opp_1Record.RecordView
      | fieldName          | value      |
      | service_start_date | 12/31/2019 |

    # Edit RLI record in the Revenue Line Items subpanel of opportunity record view
    When I click on Edit button for *RLI_1 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
    When I set values for *RLI_1 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
      | fieldName          | value          |
      | sales_stage        | Needs Analysis |
      | service_start_date | 12/20/2019     |
    When I click on Save button for *RLI_1 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
    When I close alert

    # Verify value of service_start_date field
    Then I verify fields on #Opp_1Record.RecordView
      | fieldName          | value      |
      | service_start_date | 12/20/2019 |


  @multiple_RLI_renewal_oppertunity_cleanup @AT-328
  Scenario: Opportunities >  Select all records > Mass Delete
    # Issue with seedbed not cleaning up created rewable opportunities and RLIs operation below handles the clean up. remove when fixed.
    When I choose Opportunities in modules menu
    When I toggleAll records in #OpportunitiesList.ListView
    When I select "Delete Selected" action in #OpportunitiesList.ListView
    When I Confirm confirmation alert
    When I close alert


  @SS-316 @SS-317 @pr
  Scenario: Service Opportunity > Change Service Start Date of RLIs from Opportunity level
    Given Accounts records exist:
      | *   | name      |
      | A_1 | Account_1 |
    And Opportunities records exist related via opportunities link to *A_1:
      | *     | name  |
      | Opp_1 | Opp_1 |
    And RevenueLineItems records exist related via revenuelineitems link to *Opp_1:
      | *name | date_closed | worst_case | likely_case | best_case | sales_stage         | quantity | service | service_start_date | service_duration_value | service_duration_unit | renewable |
      | RLI_1 | now         | 1000       | 2000        | 3000      | Prospecting         | 1        | true    | 2020-01-01         | 2                      | year                  | true      |
      | RLI_2 | now         | 1000       | 2000        | 3000      | Closed Lost         | 1        | true    | 2020-01-02         | 2                      | year                  | false     |
      | RLI_3 | now         | 1000       | 2000        | 3000      | Needs Analysis      | 1        | true    | 2020-01-03         | 2                      | year                  | true      |
      | RLI_4 | now         | 1000       | 2000        | 3000      | Perception Analysis | 1        | false   |                    |                        |                       |           |
      | RLI_5 | now         | 1000       | 2000        | 3000      | Closed Won          | 1        | true    | 2020-01-05         | 2                      | year                  | true      |
    
    # Navigate to Opportunities module
    When I choose Opportunities in modules menu
    When I select *Opp_1 in #OpportunitiesList.ListView

    # Verify value of 'service_start_date' field
    Then I verify fields on #Opp_1Record.RecordView
      | fieldName          | value      |
      | service_start_date | 01/01/2020 |

    # Edit 'service_start_date' field of the opportunity
    When I click Edit button on #Opp_1Record header
    When I provide input for #Opp_1Record.RecordView view
      | service_start_date |
      | 01/15/2020         |
    When I click Save button on #Opp_1Record header
    When I close alert
    
    # Verify value of 'service_start_date' field in linked RLI records
    When I open the revenuelineitems subpanel on #Opp_1Record view
    Then I verify fields for *RLI_1 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
      | fieldName          | value      |
      | service_start_date | 01/15/2020 |
    Then I verify fields for *RLI_2 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
      | fieldName          | value      |
      | service_start_date | 01/02/2020 |
    Then I verify fields for *RLI_3 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
      | fieldName          | value      |
      | service_start_date | 01/15/2020 |
    Then I verify fields for *RLI_4 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
      | fieldName          | value |
      | service_start_date |       |
    Then I verify fields for *RLI_5 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
      | fieldName          | value      |
      | service_start_date | 01/05/2020 |

    When I choose Opportunities in modules menu
    When I click on preview button on *Opp_1 in #OpportunitiesList.ListView
    When I click on Edit button in #Opp_1Preview.PreviewHeaderView
    When I provide input for #Opp_1Preview.PreviewView view
      | service_start_date |
      | 02/01/2020         |
    When I click on Save button in #Opp_1Preview.PreviewHeaderView
    When I close alert

    When I select *Opp_1 in #OpportunitiesList.ListView
    # Verify value of 'service_start_date' field in linked RLI records
    When I open the revenuelineitems subpanel on #Opp_1Record view
    Then I verify fields for *RLI_1 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
      | fieldName          | value      |
      | service_start_date | 02/01/2020 |
    Then I verify fields for *RLI_2 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
      | fieldName          | value      |
      | service_start_date | 01/02/2020 |
    Then I verify fields for *RLI_3 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
      | fieldName          | value      |
      | service_start_date | 02/01/2020 |
    Then I verify fields for *RLI_4 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
      | fieldName          | value |
      | service_start_date |       |
    Then I verify fields for *RLI_5 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
      | fieldName          | value      |
      | service_start_date | 01/05/2020 |

    # Change Sales Stage of some RLIs to "Close Won"
    When I perform mass update of RevenueLineItems [*RLI_1, *RLI_3, *RLI_4] with the following values:
      | fieldName   | value      |
      | sales_stage | Closed Won |

    # Navigate to Opportunities module
    When I choose Opportunities in modules menu
    When I select *Opp_1 in #OpportunitiesList.ListView

    # Verify value of 'service_start_date' field
    Then I verify fields on #Opp_1Record.RecordView
      | fieldName          | value      |
      | service_start_date | 01/05/2020 |

    # Change Sales Stage of some RLIs to "Close Lost"
    When I perform mass update of RevenueLineItems [*RLI_1, *RLI_3, *RLI_4, *RLI_5] with the following values:
      | fieldName   | value       |
      | sales_stage | Closed Lost |

    # Navigate to Opportunities module
    When I choose Opportunities in modules menu
    When I select *Opp_1 in #OpportunitiesList.ListView

    # Verify value of 'service_start_date' field
    Then I verify fields on #Opp_1Record.RecordView
      | fieldName          | value |
      | service_start_date |       |

    # Verify value of 'service_start_date' field in linked RLI records
    When I open the revenuelineitems subpanel on #Opp_1Record view
    Then I verify fields for *RLI_1 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
      | fieldName          | value      |
      | service_start_date | 02/01/2020 |
    Then I verify fields for *RLI_2 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
      | fieldName          | value      |
      | service_start_date | 01/02/2020 |
    Then I verify fields for *RLI_3 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
      | fieldName          | value      |
      | service_start_date | 02/01/2020 |
    Then I verify fields for *RLI_4 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
      | fieldName          | value |
      | service_start_date |       |
    Then I verify fields for *RLI_5 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
      | fieldName          | value      |
      | service_start_date | 01/05/2020 |


  @multiple_RLI_renewal_oppertunity_cleanup @AT-328 @pr
  Scenario: Opportunities >  Select all records > Mass Delete
    # Issue with seedbed not cleaning up created rewable opportunities and RLIs operation below handles the clean up. remove when fixed.
    When I choose Opportunities in modules menu
    When I toggleAll records in #OpportunitiesList.ListView
    When I select "Delete Selected" action in #OpportunitiesList.ListView
    When I Confirm confirmation alert
    When I close alert

