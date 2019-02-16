# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@modules @Opportunities @ci-excluded
Feature: Opportunities Only mode

  Background:
    Given I use default account
    Given I launch App

  @oppsPlusRLIs_2_opps_conversion @job4
  Scenario: Opportunities Only Mode > Create Opportunity > Cancel/Save
    Given Accounts records exist:
      | *name |
      | Acc_1 |

    # Switch to Opportunities Only mode
    Given I configure Opportunities mode
      | name            | value         |
      | opps_view_by    | Opportunities |
      | opps_close_date | earliest      |

    Given I open about view and login
    When I choose Opportunities in modules menu

    # Create new opportunity in Opportunities Only mode > Cancel
    When I click Create button on #OpportunitiesList header
    When I click show more button on #OpportunitiesDrawer view
    When I provide input for #OpportunitiesDrawer.HeaderView view
      | *     | name  |
      | Opp_1 | Opp_1 |
    When I provide input for #OpportunitiesDrawer.RecordView view
      | *     | date_closed | amount | worst_case | best_case | account_name | sales_stage    |
      | Opp_1 | 12/12/2020  | 200    | 150        | 250       | Acc_1        | Needs Analysis |
    When I click Cancel button on #OpportunitiesDrawer header

    # Verify that no opportunity record is created
    Then I verify number of records in #OpportunitiesList.ListView is 0

    # Create new opportunity in Opportunities Only mode > Save
    When I click Create button on #OpportunitiesList header
    When I click show more button on #OpportunitiesDrawer view
    When I provide input for #OpportunitiesDrawer.HeaderView view
      | *     | name  |
      | Opp_1 | Opp_1 |
    When I provide input for #OpportunitiesDrawer.RecordView view
      | *     | date_closed | amount | worst_case | best_case | account_name | sales_stage    | description        | lead_source       | opportunity_type | next_step |
      | Opp_1 | 12/12/2020  | 200    | 150        | 250       | Acc_1        | Needs Analysis | Great Opportunity! | Existing Customer | New Business     | Sell It!  |
    When I click Save button on #OpportunitiesDrawer header
    When I close alert

    # Verify that Opportunity is successfully created
    Then Opportunities *Opp_1 should have the following values:
#    When I click on preview button on *Opp_1 in #OpportunitiesList.ListView
#    Then I should see #Opp_1Preview view
#    Then I verify fields on #Opp_1Preview.PreviewView
      | fieldName        | value              |
      | name             | Opp_1              |
      | best_case        | $250.00            |
      | amount           | $200.00            |
      | worst_case       | $150.00            |
      | date_closed      | 12/12/2020         |
      | sales_stage      | Needs Analysis     |
      | description      | Great Opportunity! |
      | lead_source      | Existing Customer  |
      | opportunity_type | New Business       |
      | next_step        | Sell It!           |

    # Switch back to Opportunities + RLI mode
    When I configure Opportunities mode
      | name            | value            |
      | opps_view_by    | RevenueLineItems |
      | opps_close_date | latest           |

    # Verify that RLI record is created in RLI module
    When I go to "RevenueLineItems" url
    Then I verify number of records in #RevenueLineItemsList.ListView is 1

    # Verify that opportunity roll-ups are correct
    When I choose Opportunities in modules menu
    When I click on preview button on *Opp_1 in #OpportunitiesList.ListView
    Then I should see #Opp_1Preview view
    Then I verify fields on #Opp_1Preview.PreviewView
      | fieldName    | value       |
      | name         | Opp_1       |
      | best_case    | $250.00     |
      | amount       | $200.00     |
      | worst_case   | $150.00     |
      | date_closed  | 12/12/2020  |
      | sales_status | In Progress |

  @oppsPlusRLIs_2_opps_conversion @job5
  Scenario: Opportunities > Verify Opps + RLI to Opps Only conversion
    Given Accounts records exist:
      | *name |
      | Acc_1 |
    Given I open about view and login
    When I choose Opportunities in modules menu
    # Create new opportunity record
    When I click Create button on #OpportunitiesList header
    When I provide input for #OpportunitiesDrawer.HeaderView view
      | *     | name           |
      | Opp_1 | My Opportunity |
    When I provide input for #OpportunitiesDrawer.RecordView view
      | *     | account_name |
      | Opp_1 | Acc_1        |
    # Provide input for the first (default) RLI
    When I provide input for #OpportunityDrawer.RLITable view for 1 row
      | *name | date_closed | best_case | sales_stage   | worst_case | quantity | likely_case |
      | RLI_1 | 12/12/2020  | 300       | Qualification | 100        | 200      | 200         |
    # Add second RLI by clicking '+' button on the first row
    When I choose addRLI on #OpportunityDrawer.RLITable view for 1 row
    # Provide input for the second RLI
    When I provide input for #OpportunityDrawer.RLITable view for 2 row
      | *name | date_closed | best_case | worst_case | sales_stage    | quantity | likely_case |
      | RLI_2 | 12/13/2020  | 500       | 300        | Needs Analysis | 10       | 400         |
    # Add third RLI by clicking '+' button on the second row
    When I choose addRLI on #OpportunityDrawer.RLITable view for 2 row
    # Provide input for the third RLI
    When I provide input for #OpportunityDrawer.RLITable view for 3 row
      | *name | date_closed | best_case | worst_case | sales_stage | quantity | likely_case |
      | RLI_3 | 12/11/2020  | 50        | 30         | Closed Won  | 10       | 40          |
    # Save new opportunity
    When I click Save button on #OpportunitiesDrawer header
    When I close alert

    # Switch to Opp Only mode
    Given I configure Opportunities mode
      | name            | value         |
      | opps_view_by    | Opportunities |
      | opps_close_date | earliest      |

    # Verify that opportunity is created correctly in Opportunities Only mode
    When I choose Opportunities in modules menu
    When I click on preview button on *Opp_1 in #OpportunitiesList.ListView
    Then I should see #Opp_1Preview view
    Then I verify fields on #Opp_1Preview.PreviewView
      | fieldName   | value          |
      | name        | My Opportunity |
      | best_case   | $840.00        |
      | amount      | $640.00        |
      | worst_case  | $440.00        |
      | date_closed | 12/12/2020     |
      | sales_stage | Qualification  |

    # Switch back to Opportunities + RLI mode
    When I configure Opportunities mode
      | name            | value            |
      | opps_view_by    | RevenueLineItems |
      | opps_close_date | latest           |

    # Verify that RLI record is created in RLI module
    When I go to "RevenueLineItems" url
    Then I verify number of records in #RevenueLineItemsList.ListView is 1

    # Verify that opportunity is updated correctly in Opps + RLI mode
    When I choose Opportunities in modules menu
    When I click on preview button on *Opp_1 in #OpportunitiesList.ListView
    Then I should see #Opp_1Preview view
    Then I verify fields on #Opp_1Preview.PreviewView
      | fieldName    | value          |
      | name         | My Opportunity |
      | best_case    | $840.00        |
      | amount       | $640.00        |
      | worst_case   | $440.00        |
      | date_closed  | 12/12/2020     |
      | sales_status | In Progress    |

  @oppsPlusRLIs_2_opps_conversion @job1
  Scenario Outline: Verify Opps + RLI to Opps Only conversion > Closed Won/Lost case
    Given Accounts records exist:
      | *name |
      | Acc_1 |

    Given I open about view and login
    # Create opportunity with 2 RLIs
    When I choose Opportunities in modules menu
    When I click Create button on #OpportunitiesList header
    When I provide input for #OpportunitiesDrawer.HeaderView view
      | *     | name           |
      | Opp_1 | My Opportunity |
    When I provide input for #OpportunitiesDrawer.RecordView view
      | *     | account_name |
      | Opp_1 | Acc_1        |
    # Provide input for the first (default) RLI
    When I provide input for #OpportunityDrawer.RLITable view for 1 row
      | *name | date_closed | sales_stage  | likely_case |
      | RLI_1 | 12/12/2020  | <SalesStage> | 150         |
    # Add second RLI by clicking '+' button on the first row
    When I choose addRLI on #OpportunityDrawer.RLITable view for 1 row
    # Provide input for the second RLI
    When I provide input for #OpportunityDrawer.RLITable view for 2 row
      | *name | date_closed | sales_stage  | likely_case |
      | RLI_2 | 12/11/2020  | <SalesStage> | 250         |
    # Save new opportunity
    When I click Save button on #OpportunitiesDrawer header
    When I close alert

    # Switch to Opp Only mode
    Given I configure Opportunities mode
      | name            | value         |
      | opps_view_by    | Opportunities |
      | opps_close_date | earliest      |

    # Verify that opportunity is created correctly in Opportunities Only mode
    When I choose Opportunities in modules menu
    When I click on preview button on *Opp_1 in #OpportunitiesList.ListView
    Then I should see #Opp_1Preview view
    Then I verify fields on #Opp_1Preview.PreviewView
      | fieldName   | value          |
      | name        | My Opportunity |
      | best_case   | $400.00        |
      | amount      | $400.00        |
      | worst_case  | $400.00        |
      | date_closed | 12/11/2020     |
      | sales_stage | <SalesStage>   |

    # Switch back to Opportunities + RLI mode
    When I configure Opportunities mode
      | name            | value            |
      | opps_view_by    | RevenueLineItems |
      | opps_close_date | latest           |

    When I choose Opportunities in modules menu
    When I click on preview button on *Opp_1 in #OpportunitiesList.ListView
    Then I should see #Opp_1Preview view
    Then I verify fields on #Opp_1Preview.PreviewView
      | fieldName    | value            |
      | name         | My Opportunity   |
      | best_case    | <ExpectedAmount> |
      | amount       | <ExpectedAmount> |
      | worst_case   | <ExpectedAmount> |
      | date_closed  | 12/11/2020       |
      | sales_status | <SalesStage>     |

    # Verify that RLI record is created in RLI module
    When I choose RevenueLineItems in modules menu
    Then I verify number of records in #RevenueLineItemsList.ListView is 1

    Examples:
      | SalesStage  | ExpectedAmount |
      | Closed Won  | $400.00        |
      | Closed Lost | $0.00          |
