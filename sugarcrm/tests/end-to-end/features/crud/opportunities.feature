# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@crud_modules_opportunities @job4
Feature: Opportunities

  Background:
    Given I use default account
    Given I launch App

  @list
  Scenario: Opportunities > List View > Preview
    # Create Opportunity
    Given Opportunities records exist:
      | *name |
      | Opp_1 |
    # Create Account. (the linking part does not work)
    Given Accounts records exist related via accounts link to *Opp_1:
      | *name |
      | Acc_1 |
    Given I open about view and login
    When I choose Opportunities in modules menu
    Then I should see *Opp_1 in #OpportunitiesList.ListView
    Then I verify fields for *Opp_1 in #OpportunitiesList.ListView
      | fieldName | value |
      | name      | Opp_1 |
    When I click on preview button on *Opp_1 in #OpportunitiesList.ListView
    Then I should see #Opp_1Preview view
    Then I verify fields on #Opp_1Preview.PreviewView
      | fieldName    | value |
      | name         | Opp_1 |
      | account_name | Acc_1 |
      | amount       | $0.00 |
      | best_case    | $0.00 |
      | worst_case   | $0.00 |
      | sales_status | New   |

  @list-search
  Scenario: Opportunities > List View > Filter > Search main input
    Given Opportunities records exist:
      | *name | opportunity_type  | lead_source       | description       |
      | Opp_1 | Existing Business | Cold Call         | Opp_1 description |
      | Opp_2 | New Business      | Existing Customer | Opp_2 description |
      | Opp_3 | Existing Business | Self Generated    | Opp_3 description |
    Given I open about view and login
    When I choose Opportunities in modules menu
    Then I should see *Opp_1 in #OpportunitiesList.ListView
    Then I should see *Opp_2 in #OpportunitiesList.ListView
    Then I should see *Opp_3 in #OpportunitiesList.ListView
    When I search for "Opp_2" in #OpportunitiesList.FilterView view
    Then I should not see *Opp_1 in #OpportunitiesList.ListView
    Then I should see *Opp_2 in #OpportunitiesList.ListView
    Then I should not see *Opp_3 in #OpportunitiesList.ListView
    Then I verify fields for *Opp_2 in #OpportunitiesList.ListView
      | fieldName        | value             |
      | name             | Opp_2             |
      | lead_source      | Existing Customer |
      | opportunity_type | New Business      |

  @list-edit
  Scenario: Opportunities > List View > Inline Edit
    Given Opportunities records exist:
      | *name | opportunity_type  | lead_source | description       |
      | Opp_1 | Existing Business | Cold Call   | Opp_1 description |
    Given Accounts records exist related via accounts link to *Opp_1:
      | *name |
      | Acc_1 |
    Given I open about view and login
    When I choose Opportunities in modules menu
    When I click on Edit button for *Opp_1 in #OpportunitiesList.ListView
    When I set values for *Opp_1 in #OpportunitiesList.ListView
      | fieldName        | value             |
      | name             | Opp_1 edited      |
      | lead_source      | Existing Customer |
      | opportunity_type | New Business      |
    When I click on Cancel button for *Opp_1 in #OpportunitiesList.ListView
    Then I verify fields for *Opp_1 in #OpportunitiesList.ListView
      | fieldName | value |
      | name      | Opp_1 |
    When I click on Edit button for *Opp_1 in #OpportunitiesList.ListView
    When I set values for *Opp_1 in #OpportunitiesList.ListView
      | fieldName        | value             |
      | name             | Opp_1 edited      |
      | lead_source      | Existing Customer |
      | opportunity_type | New Business      |
    When I click on Save button for *Opp_1 in #OpportunitiesList.ListView
    Then I verify fields for *Opp_1 in #OpportunitiesList.ListView
      | fieldName        | value             |
      | name             | Opp_1 edited      |
      | lead_source      | Existing Customer |
      | opportunity_type | New Business      |

  @list-delete
  Scenario: Opportunities > List View > Delete
    Given RevenueLineItems records exist:
      | *name | date_closed               | worst_case | likely_case | best_case | sales_stage | quantity |
      | RLI_1 | 2018-10-19T19:20:22+00:00 | 200        | 300         | 400       | Prospecting | 5        |
    Given Opportunities records exist related via opportunities link to *RLI_1:
      | *name | opportunity_type  | lead_source | description       |
      | Opp_1 | Existing Business | Cold Call   | Opp_1 description |
    Given Accounts records exist related via accounts link to *Opp_1:
      | *name |
      | Acc_1 |
    Given I open about view and login
    # Confirm that RLI related to opportunity is present
    When I choose RevenueLineItems in modules menu
    Then I should see *RLI_1 in #RevenueLineItemsList.ListView
    # Delete opportunity > Cancel
    When I choose Opportunities in modules menu
    When I click on Delete button for *Opp_1 in #OpportunitiesList.ListView
    When I Cancel confirmation alert
    Then I should see *Opp_1 in #OpportunitiesList.ListView
    # Delete opportunity > Confirm
    When I click on Delete button for *Opp_1 in #OpportunitiesList.ListView
    When I Confirm confirmation alert
    Then I should see #OpportunitiesList view
    Then I should not see *Opp_1 in #OpportunitiesList.ListView
    # Verify that RLI related to opportunity is also deleted
    When I choose RevenueLineItems in modules menu
    Then I should not see *RLI_1 in #RevenueLineItemsList.ListView


  @delete
  Scenario: Opportunities >  Record View > Delete
    Given RevenueLineItems records exist:
      | *name | date_closed               | worst_case | likely_case | best_case | sales_stage | quantity |
      | RLI_1 | 2018-10-19T19:20:22+00:00 | 200        | 300         | 400       | Prospecting | 5        |
    Given Opportunities records exist related via opportunities link to *RLI_1:
      | *name | opportunity_type  | lead_source | description       |
      | Opp_1 | Existing Business | Cold Call   | Opp_1 description |
    Given Accounts records exist related via accounts link to *Opp_1:
      | *name |
      | Acc_1 |
    Given I open about view and login
    When I choose Opportunities in modules menu
    When I select *Opp_1 in #OpportunitiesList.ListView
    When I open actions menu in #Opp_1Record
    When I choose Delete from actions menu in #Opp_1Record
    When I Cancel confirmation alert
    Then I should see #Opp_1Record view
    Then I verify fields on #Opp_1Record.HeaderView
      | fieldName | value |
      | name      | Opp_1 |
    Then I verify fields on #Opp_1Record.RecordView
      | fieldName    | value |
      | sales_status | New   |
    When I open actions menu in #Opp_1Record
    When I choose Delete from actions menu in #Opp_1Record
    When I Confirm confirmation alert
    Then I should see #OpportunitiesList.ListView view
    Then I should not see *Opp_1 in #OpportunitiesList.ListView
    # Verify that RLI related to opportunity is also deleted
    When I choose RevenueLineItems in modules menu
    Then I should not see *RLI_1 in #RevenueLineItemsList.ListView


  @copy
  Scenario: Opportunities > Record view > Copy
    Given RevenueLineItems records exist:
      | *name | date_closed               | worst_case | likely_case | best_case | sales_stage | quantity |
      | RLI_1 | 2018-10-19T19:20:22+00:00 | 200        | 300         | 400       | Prospecting | 5        |
    Given Opportunities records exist related via opportunities link to *RLI_1:
      | *name | opportunity_type  | lead_source | description       |
      | Opp_1 | Existing Business | Cold Call   | Opp_1 description |
    Given Accounts records exist related via accounts link to *Opp_1:
      | *name |
      | Acc_1 |
    Given I open about view and login
    When I choose Opportunities in modules menu
    When I select *Opp_1 in #OpportunitiesList.ListView
    When I click show more button on #Opp_1Record view
    # Copy > Cancel
    When I open actions menu in #Opp_1Record
    When I choose Copy from actions menu in #Opp_1Record
    When I provide input for #OpportunitiesDrawer.HeaderView view
      | name       |
      | Alex's Opp |
    When I provide input for #OpportunitiesDrawer.RecordView view
      | lead_source       | opportunity_type |
      | Existing Customer | New Business     |
    When I click Cancel button on #OpportunitiesDrawer header
    Then I verify fields on #Opp_1Record.HeaderView
      | fieldName | value |
      | name      | Opp_1 |
    Then I verify fields on #Opp_1Record.RecordView
      | fieldName        | value             |
      | lead_source      | Cold Call         |
      | opportunity_type | Existing Business |
    # Copy > Save
    When I open actions menu in #Opp_1Record
    When I choose Copy from actions menu in #Opp_1Record
    When I provide input for #OpportunitiesDrawer.HeaderView view
      | *     | name       |
      | Opp_2 | Alex's Opp |
    When I provide input for #OpportunitiesDrawer.RecordView view
      | *     | lead_source       | opportunity_type |
      | Opp_2 | Existing Customer | New Business     |
    # Opportunity requires at least one RLI
    When I provide input for #OpportunityDrawer.RLITable view for 1 row
      | name  | date_closed | best_case | sales_stage   | quantity | likely_case |
      | RLI_2 | 12/12/2020  | 300       | Qualification | 5        | 200         |
    When I click Save button on #OpportunitiesDrawer header
    When I close alert
    When I click show more button on #Opp_2Record view
    Then I verify fields on #Opp_2Record.HeaderView
      | fieldName | value      |
      | name      | Alex's Opp |
    Then I verify fields on #Opp_2Record.RecordView
      | fieldName        | value             |
      | lead_source      | Existing Customer |
      | opportunity_type | New Business      |
      | amount           | $200.00           |

  @edit
  Scenario: Opportunities > Record View > Edit
    Given RevenueLineItems records exist:
      | *name | date_closed               | worst_case | likely_case | best_case | sales_stage | quantity |
      | RLI_1 | 2018-10-19T19:20:22+00:00 | 200        | 300         | 400       | Prospecting | 5        |
    Given Opportunities records exist related via opportunities link to *RLI_1:
      | *name | opportunity_type  | lead_source | description       |
      | Opp_1 | Existing Business | Cold Call   | Opp_1 description |
    Given Accounts records exist related via accounts link to *Opp_1:
      | *name |
      | Acc_1 |
    Given I open about view and login
    When I choose Opportunities in modules menu
    When I select *Opp_1 in #OpportunitiesList.ListView
    When I click show more button on #Opp_1Record view
    # Edit > Cancel
    When I click Edit button on #Opp_1Record header
    When I provide input for #Opp_1Record.HeaderView view
      | name            |
      | New Opportunity |
    When I provide input for #Opp_1Record.RecordView view
      | description        | lead_source       | opportunity_type |
      | Great Opportunity! | Existing Customer | New Business     |
    When I click Cancel button on #Opp_1Record header
    Then I verify fields on #Opp_1Record.HeaderView
      | fieldName | value |
      | name      | Opp_1 |
    Then I verify fields on #Opp_1Record.RecordView
      | fieldName        | value             |
      | description      | Opp_1 description |
      | lead_source      | Cold Call         |
      | opportunity_type | Existing Business |
    # Edit > Save
    When I click Edit button on #Opp_1Record header
    Then I should see #Opp_1Record view
    When I provide input for #Opp_1Record.HeaderView view
      | name            |
      | New Opportunity |
    When I provide input for #Opp_1Record.RecordView view
      | description        | lead_source       | opportunity_type |
      | Great Opportunity! | Existing Customer | New Business     |
    When I click Save button on #Opp_1Record header
    When I close alert
    Then I verify fields on #Opp_1Record.HeaderView
      | fieldName | value           |
      | name      | New Opportunity |
    Then I verify fields on #Opp_1Record.RecordView
      | fieldName        | value              |
      | description      | Great Opportunity! |
      | lead_source      | Existing Customer  |
      | opportunity_type | New Business       |

  @create_opportunity @pr
  Scenario: Opportunities >  Create opportunity with RLIs
    Given Accounts records exist:
      | *name |
      | Acc_1 |
    Given I open about view and login
    When I choose Opportunities in modules menu
    When I click Create button on #OpportunitiesList header
    When I provide input for #OpportunitiesDrawer.HeaderView view
      | *     | name                  |
      | Opp_1 | CreateOpportunityTest |
    When I provide input for #OpportunitiesDrawer.RecordView view
      | *     | account_name |
      | Opp_1 | Acc_1        |
    # Provide input for the first (default) RLI
    When I provide input for #OpportunityDrawer.RLITable view for 1 row
      | *name | date_closed | best_case | sales_stage   | quantity | likely_case |
      | RLI1  | 12/12/2020  | 300       | Qualification | 5        | 200         |
    # Add second RLI by clicking '+' button on the first row
    When I choose addRLI on #OpportunityDrawer.RLITable view for 1 row
    # Provide input for the second RLI
    When I provide input for #OpportunityDrawer.RLITable view for 2 row
      | *name | date_closed | best_case | sales_stage    | quantity | likely_case |
      | RLI2  | 12/12/2021  | 500       | Needs Analysis | 10       | 400         |
    # Add third RLI by clicking '+' button on the second row
    When I choose addRLI on #OpportunityDrawer.RLITable view for 2 row
    # Provide input for the third RLI
    When I provide input for #OpportunityDrawer.RLITable view for 3 row
      | *name | date_closed | best_case | sales_stage       | quantity | likely_case |
      | RLI3  | 12/12/2022  | 50        | Value Proposition | 10       | 40          |
    # Remove first RLI
    When I choose removeRLI on #OpportunityDrawer.RLITable view for 1 row
    # Save new opportunity
    When I click Save button on #OpportunitiesDrawer header
    When I close alert
    # Verify data
    When I click on preview button on *Opp_1 in #OpportunitiesList.ListView
    Then I should see #Opp_1Preview view
    Then I verify fields on #Opp_1Preview.PreviewView
      | fieldName    | value                 |
      | name         | CreateOpportunityTest |
      | best_case    | $550.00               |
      | amount       | $440.00               |
      | worst_case   | $440.00               |
      | date_closed  | 12/12/2022            |
      | sales_status | In Progress           |
    When I choose RevenueLineItems in modules menu
    Then I should see *RLI2 in #RevenueLineItemsList.ListView
    Then I should see *RLI3 in #RevenueLineItemsList.ListView

  @change_rli_currency_when_opp_is_created
  Scenario: Opportunities >  Change currency of the RLI when creating new opportunity
    # Create 3 product records
    Given 3 ProductTemplates records exist:
      | *name          | discount_price | cost_price | list_price |
      | Prod_{{index}} | 100            | 200        | 300        |
    # Create Account
    Given Accounts records exist:
      | *name |
      | Acc_1 |

    Given I open about view and login
    # 2. Add new 'Euro' currency to Sugar instance
    When I go to "Currencies" url
    When I click Create button on #CurrenciesList header
    When I provide input for #CurrenciesDrawer.RecordView view
      | iso4217 | conversion_rate |
      | EUR     | 0.5             |
    When I click Save button on #CurrenciesDrawer header
    When I close alert

    # 3. Add new 'Rubles' currency to Sugar instance
    When I click Create button on #CurrenciesList header
    When I provide input for #CurrenciesDrawer.RecordView view
      | iso4217 | conversion_rate |
      | RUB     | 1.5             |
    When I click Save button on #CurrenciesDrawer header
    When I close alert

    # 4. Change 'Prod_1' product's currency to EUR
    When I go to "ProductTemplates" url
    When I select *Prod_1 in #ProductTemplatesList.ListView
    Then I should see #Prod_1Record view
    When I click Edit button on #Prod_1Record header
    When I provide input for #Prod_1Record.RecordView view
      | currency_id |
      | € (EUR)     |
    When I click Save button on #Prod_1Record header
    When I close alert

    # 5. Change 'Prod_2' product's currency to RUB
    When I go to "ProductTemplates" url
    When I select *Prod_2 in #ProductTemplatesList.ListView
    Then I should see #Prod_2Record view
    When I click Edit button on #Prod_2Record header
    When I provide input for #Prod_2Record.RecordView view
      | currency_id |
      | руб (RUB)   |
    When I click Save button on #Prod_2Record header
    When I close alert

    # Create opportunity records from products
    When I choose Opportunities in modules menu
    When I click Create button on #OpportunitiesList header
    When I provide input for #OpportunitiesDrawer.HeaderView view
      | *     | name                  |
      | Opp_1 | CreateOpportunityTest |
    When I provide input for #OpportunitiesDrawer.RecordView view
      | *     | account_name |
      | Opp_1 | Acc_1        |
    # Provide input for the first (default) RLI
    When I provide input for #OpportunityDrawer.RLITable view for 1 row
      | *name | date_closed | product_template_name | currency_id |
      | RLI1  | 12/12/2020  | Prod_1                | € (EUR)     |

    # Add second RLI by clicking '+' button on the first row
    When I choose addRLI on #OpportunityDrawer.RLITable view for 1 row
    # Provide input for the second RLI
    When I provide input for #OpportunityDrawer.RLITable view for 2 row
      | *name | date_closed | product_template_name | currency_id |
      | RLI2  | 12/12/2021  | Prod_2                | € (EUR)     |
    # Add third RLI by clicking '+' button on the second row
    When I choose addRLI on #OpportunityDrawer.RLITable view for 2 row
    # Provide input for the third RLI
    When I provide input for #OpportunityDrawer.RLITable view for 3 row
      | *name | date_closed | product_template_name | currency_id |
      | RLI3  | 12/12/2022  | Prod_3                | € (EUR)     |

    # Save new opportunity
    When I click Save button on #OpportunitiesDrawer header
    When I close alert

    # Verify opportunity data
    When I click on preview button on *Opp_1 in #OpportunitiesList.ListView
    Then I should see #Opp_1Preview view
    Then I verify fields on #Opp_1Preview.PreviewView
      | fieldName    | value                 |
      | name         | CreateOpportunityTest |
      | best_case    | $300.00               |
      | amount       | $300.00               |
      | worst_case   | $300.00               |
      | date_closed  | 12/12/2022            |
      | sales_status | In Progress           |

    # Verify that created RLI records are present in RLI list view and values are presented in Euro currency
    # This proves that currency selector was functional during opportunity creation.
    When I select *Opp_1 in #OpportunitiesList.ListView
    When I open the revenuelineitems subpanel on #Opp_1Record view
    Then I verify number of records in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems is 3

    Then I should see *RLI1 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
    Then I verify fields for *RLI1 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
      | fieldName   | value          |
      | name        | RLI1           |
      | likely_case | €50.00 $100.00 |
      | best_case   | €50.00 $100.00 |
      | worst_case  | €50.00 $100.00 |

    Then I should see *RLI2 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
    Then I verify fields for *RLI2 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
      | fieldName   | value          |
      | name        | RLI2           |
      | likely_case | €50.00 $100.00 |
      | best_case   | €50.00 $100.00 |
      | worst_case  | €50.00 $100.00 |

    Then I should see *RLI3 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
    Then I verify fields for *RLI3 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
      | fieldName   | value          |
      | name        | RLI3           |
      | likely_case | €50.00 $100.00 |
      | best_case   | €50.00 $100.00 |
      | worst_case  | €50.00 $100.00 |

