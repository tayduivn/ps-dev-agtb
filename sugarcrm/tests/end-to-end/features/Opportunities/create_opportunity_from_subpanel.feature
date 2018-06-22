# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@subpanels @create_opportunity
Feature: Subpanel Support

  Background:
    Given I use default account
    Given I launch App

  @create_opportunity_record_from_AccountRecordView_Opportunities_Subpanel
  Scenario: Account Record View > Opportunity Subpanel > Create Opportunity > Cancel
    Given Accounts records exist:
      | *name     | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country |
      | Account_A | City 1               | Street address here    | 22051                      | WA                    | USA                     |
    Given I open about view and login
    When I choose Accounts in modules menu
    Then I should see *Account_A in #AccountsList.ListView
    When I select *Account_A in #AccountsList.ListView

    # Create Opportunity record from Opportunities subpanel of account record view
    When I open the opportunities subpanel on #Account_ARecord view
    When I create_new record from opportunities subpanel on #Account_ARecord view

    # Verify that account field is auto-populated from the 'parent' account
    Then I verify fields on #OpportunitiesRecord.RecordView
      | fieldName    | value     |
      | account_name | Account_A |

    # Populate required fields
    When I provide input for #OpportunitiesDrawer.HeaderView view
      | *    | name                  |
      | Opp1 | CreateOpportunityTest |
    When I provide input for #OpportunitiesDrawer.RecordView view
      | *    |
      | Opp1 |
    # Provide input for the first (default) RLI
    When I provide input for #OpportunityDrawer.RLITable view for 1 row
      | name | date_closed | best_case | sales_stage   | quantity | likely_case |
      | RLI1 | 12/12/2020  | 300       | Qualification | 5        | 200         |
    # Add second RLI by clicking '+' button on the first row
    When I choose addRLI on #OpportunityDrawer.RLITable view for 1 row
    # Provide input for the second RLI
    And I provide input for #OpportunityDrawer.RLITable view for 2 row
      | name | date_closed | best_case | sales_stage   | quantity | likely_case |
      | RLI2 | 12/12/2021  | 500       | Qualification | 10       | 400         |
    # Add third RLI by clicking '+' button on the second row
    When I choose addRLI on #OpportunityDrawer.RLITable view for 2 row
    # Provide input for the third RLI
    And I provide input for #OpportunityDrawer.RLITable view for 3 row
      | name | date_closed | best_case | sales_stage   | quantity | likely_case |
      | RLI3 | 12/12/2022  | 50        | Qualification | 10       | 40          |
    # Cancel new opportunity creation
    When I click Cancel button on #OpportunitiesDrawer header

    # Verify no opportunity record is created
    Then I should see #Account_ARecord view
    Then I verify number of records in #Account_ARecord.SubpanelsLayout.subpanels.opportunities is 0

    # Verify no RLI(s) record is created
    Then I should see #Account_ARecord view
    Then I verify number of records in #Account_ARecord.SubpanelsLayout.subpanels.revenuelineitems is 0


  @create_opportunity_record_from_AccountRecordView_Opportunities_Subpanel
  Scenario: Account Record View > Opportunity Subpanel > Create Opportunity > Save
    Given Accounts records exist:
      | *name     | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country |
      | Account_A | City 1               | Street address here    | 22051                      | WA                    | USA                     |
    Given I open about view and login
    When I choose Accounts in modules menu
    Then I should see *Account_A in #AccountsList.ListView
    When I select *Account_A in #AccountsList.ListView

    # Create Opportunity record from Opportunities subpanel of account record view
    When I open the opportunities subpanel on #Account_ARecord view
    When I create_new record from opportunities subpanel on #Account_ARecord view

    # Verify that account field is auto-populated from the 'parent' account
    Then I verify fields on #OpportunitiesRecord.RecordView
      | fieldName    | value     |
      | account_name | Account_A |

    # Populate required fields
    When I provide input for #OpportunitiesDrawer.HeaderView view
      | *    | name                  |
      | Opp1 | CreateOpportunityTest |
    When I provide input for #OpportunitiesDrawer.RecordView view
      | *    |
      | Opp1 |
    # Provide input for the first (default) RLI
    When I provide input for #OpportunityDrawer.RLITable view for 1 row
      | *    | name | date_closed | best_case | sales_stage   | quantity | likely_case |
      | RLI1 | RLI1 | 12/12/2020  | 300       | Qualification | 5        | 200         |
    # Add second RLI by clicking '+' button on the first row
    When I choose addRLI on #OpportunityDrawer.RLITable view for 1 row
    # Provide input for the second RLI
    And I provide input for #OpportunityDrawer.RLITable view for 2 row
      | *name | date_closed | best_case | sales_stage    | quantity | likely_case |
      | RLI2  | 12/12/2021  | 500       | Needs Analysis | 10       | 400         |
    # Add third RLI by clicking '+' button on the second row
    When I choose addRLI on #OpportunityDrawer.RLITable view for 2 row
    # Provide input for the third RLI
    And I provide input for #OpportunityDrawer.RLITable view for 3 row
      | *name | date_closed | best_case | sales_stage       | quantity | likely_case |
      | RLI3  | 12/12/2022  | 150       | Value Proposition | 10       | 100         |
    # Save new opportunity
    When I click Save button on #OpportunitiesDrawer header
    And I close alert

    # Verify opportunity record is created
    Then I should see #Account_ARecord view
    Then I verify fields for *Opp1 in #Account_ARecord.SubpanelsLayout.subpanels.opportunities
      | fieldName    | value                 |
      | name         | CreateOpportunityTest |
      | date_closed  | 12/12/2022            |
      | amount       | $700.00               |
      | sales_status | In Progress           |

    # Verify  RLI records are created
    Then I should see #Account_ARecord view
    When I open the revenuelineitems subpanel on #Account_ARecord view
    Then I verify number of records in #Account_ARecord.SubpanelsLayout.subpanels.revenuelineitems is 3
    # TODO:  Add Ability to access created RLIs by their record ID
#    Then I verify fields for *RLI1 in #Account_ARecord.SubpanelsLayout.subpanels.revenuelineitems
#      | fieldName | value |
#      | name      | RLI1  |

  @create_opportunity_record_from_OpportunityRecordView_Opportunities_Subpanel
  Scenario: Contact Record View > Opportunity Subpanel > Create Opportunity > Cancel
    Given Contacts records exist:
      | *    | first_name | last_name | email                                          |
      | Alex | Alex       | Nisevich  | alex1@example.org (primary), alex2@example.org |
    Given Accounts records exist related via accounts link:
      | *name     | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country |
      | Account_A | Cupertino            | 10050 N Wolfe Rd       | 95014                      | CA                    | US                      |
    Given I open about view and login
    When I choose Contacts in modules menu
    Then I should see *Alex in #ContactsList.ListView
    When I select *Alex in #ContactsList.ListView

    # Create Opportunity record from Opportinities subpanel of contact record view
    When I open the opportunities subpanel on #AlexRecord view
    When I create_new record from opportunities subpanel on #AlexRecord view

    # Verify that account field is auto-populated from the 'parent' account
    Then I verify fields on #OpportunitiesRecord.RecordView
      | fieldName    | value      |
      | account_name | ,Account_A |

    # Populate required fields
    When I provide input for #OpportunitiesDrawer.HeaderView view
      | *    | name                  |
      | Opp1 | CreateOpportunityTest |
    When I provide input for #OpportunitiesDrawer.RecordView view
      | *    |
      | Opp1 |
    # Provide input for the first (default) RLI
    When I provide input for #OpportunityDrawer.RLITable view for 1 row
      | name | date_closed | best_case | sales_stage   | quantity | likely_case |
      | RLI1 | 12/12/2020  | 300       | Qualification | 5        | 200         |
    # Add second RLI by clicking '+' button on the first row
    When I choose addRLI on #OpportunityDrawer.RLITable view for 1 row
    # Provide input for the second RLI
    And I provide input for #OpportunityDrawer.RLITable view for 2 row
      | name | date_closed | best_case | sales_stage    | quantity | likely_case |
      | RLI2 | 12/12/2021  | 500       | Needs Analysis | 10       | 400         |
    # Add third RLI by clicking '+' button on the second row
    When I choose addRLI on #OpportunityDrawer.RLITable view for 2 row
    # Provide input for the third RLI
    And I provide input for #OpportunityDrawer.RLITable view for 3 row
      | name | date_closed | best_case | sales_stage       | quantity | likely_case |
      | RLI3 | 12/12/2022  | 150       | Value Proposition | 10       | 100         |
    # Cancel new opportunity creation
    When I click Cancel button on #OpportunitiesDrawer header

    # Verify no opportunity record is created
    Then I should see #AlexRecord view
    Then I verify number of records in #AlexRecord.SubpanelsLayout.subpanels.opportunities is 0

    # Verify no RLI(s) record is created
    Then I should see #Account_ARecord view
    Then I verify number of records in #Account_ARecord.SubpanelsLayout.subpanels.revenuelineitems is 0

  @create_opportunity_record_from_OpportunityRecordView_Opportunities_Subpanel
  Scenario: Contact Record View > Opportunity Subpanel > Create Opportunity > Save
    Given Contacts records exist:
      | *    | first_name | last_name | email                                          |
      | Alex | Alex       | Nisevich  | alex1@example.org (primary), alex2@example.org |
    Given Accounts records exist related via accounts link:
      | *name     | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country |
      | Account_A | Cupertino            | 10050 N Wolfe Rd       | 95014                      | CA                    | US                      |
    Given I open about view and login
    When I choose Contacts in modules menu
    Then I should see *Alex in #ContactsList.ListView
    When I select *Alex in #ContactsList.ListView

    # Create Opportunity record from Opportinities subpanel of contact record view
    When I open the opportunities subpanel on #AlexRecord view
    When I create_new record from opportunities subpanel on #AlexRecord view

    # Verify that account field is auto-populated from the 'parent' account
    Then I verify fields on #OpportunitiesRecord.RecordView
      | fieldName    | value      |
      | account_name | ,Account_A |

    # Populate required fields
    When I provide input for #OpportunitiesDrawer.HeaderView view
      | *    | name                  |
      | Opp1 | CreateOpportunityTest |
    When I provide input for #OpportunitiesDrawer.RecordView view
      | *    |
      | Opp1 |
    # Provide input for the first (default) RLI
    When I provide input for #OpportunityDrawer.RLITable view for 1 row
      | name | date_closed | best_case | sales_stage   | quantity | likely_case |
      | RLI1 | 12/12/2020  | 300       | Qualification | 5        | 200         |
    # Add second RLI by clicking '+' button on the first row
    When I choose addRLI on #OpportunityDrawer.RLITable view for 1 row
    # Provide input for the second RLI
    And I provide input for #OpportunityDrawer.RLITable view for 2 row
      | name | date_closed | best_case | sales_stage    | quantity | likely_case |
      | RLI2 | 12/12/2021  | 500       | Needs Analysis | 10       | 400         |
    # Add third RLI by clicking '+' button on the second row
    When I choose addRLI on #OpportunityDrawer.RLITable view for 2 row
      # Provide input for the third RLI
    And I provide input for #OpportunityDrawer.RLITable view for 3 row
      | name | date_closed | best_case | sales_stage       | quantity | likely_case |
      | RLI3 | 12/12/2022  | 150       | Value Proposition | 10       | 100         |
    # Save new opportunity
    When I click Save button on #OpportunitiesDrawer header
    And I close alert
    # Verify opportunity record is created
    Then I should see #AlexRecord view
    Then I verify fields for *Opp1 in #AlexRecord.SubpanelsLayout.subpanels.opportunities
      | fieldName    | value                 |
      | name         | CreateOpportunityTest |
      | date_closed  | 12/12/2022            |
      | amount       | $700.00               |
      | sales_status | In Progress           |

