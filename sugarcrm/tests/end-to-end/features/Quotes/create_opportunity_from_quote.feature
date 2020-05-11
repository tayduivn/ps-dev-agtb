# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@modules @quotes-group @job1
Feature: Create Opportunity from Quote

  Background:
    Given I use default account
    Given I launch App

  @create_opportunity_from_quote @pr
  Scenario: Quotes > Record view > Create Opportunity from Quote
    # Create a quote
    Given Quotes records exist:
      | *name   | date_quote_expected_closed | quote_stage |
      | Quote_1 | 2018-10-19T19:20:22+00:00  | Negotiation |
    # Create Account
    Given Accounts records exist related via billing_accounts link to *Quote_1:
      | name  |
      | Acc_1 |
    # Create a product bundle
    Given ProductBundles records exist related via product_bundles link to *Quote_1:
      | *name   |
      | Group_1 |
    # Add QLI
    Given Products records exist related via products link:
      | *name | discount_price | discount_amount | discount_select | quantity |
      | QLI_1 | 100            | 1               | false           | -2.00    |
      | QLI_2 | 200            | 2               | true            | 1.00     |
    Given I open about view and login

    When I choose Quotes in modules menu
    When I select *Quote_1 in #QuotesList.ListView
    Then I should see #Quote_1Record view
    # Create Opportunity from Quote
    When I open actions menu in #Quote_1Record
    When I choose CreateOpportunity from actions menu in #Quote_1Record
    Then I should see #OpportunitiesRecord view
    Then I verify fields on #OpportunitiesRecord.HeaderView
      | fieldName | value   |
      | name      | Quote_1 |
    When I click show more button on #OpportunitiesRecord view
    Then I verify fields on #OpportunitiesRecord.RecordView
      | fieldName        | value        |
      | date_closed      | 10/19/2018   |
      | account_name     | Acc_1        |
      | sales_status     | In Progress  |
      | amount           | $-3.00       |
      | best_case        | $-3.00       |
      | worst_case       | $-3.00       |
      | opportunity_type | New Business |

    # Assign id for newly created opportunity record
    When I filter for the Opportunities record *Opp_1 named "Quote_1"

    # Assign id for newly created RLI record named 'QLI_1'
    When I filter for the RevenueLineItems record *RLI_1 named "QLI_1"
    When I select *RLI_1 in #RevenueLineItemsList.ListView
    Then I verify fields on #RLI_1Record.RecordView
      | fieldName       | value |
      | discount_amount | $1.00 |
      | quantity        | -2.00 |


    # Assign id for newly created RLI record named 'QLI_2'
    When I filter for the RevenueLineItems record *RLI_2 named "QLI_2"
    When I select *RLI_2 in #RevenueLineItemsList.ListView
    Then I verify fields on #RLI_2Record.RecordView
      | fieldName       | value |
      | discount_amount | 2.00% |
      | quantity        | 1.00  |

    # Verify discount_amount field in created RLIs in RLI subpanel of Opportunity record view
    When I choose Opportunities in modules menu
    When I select *Opp_1 in #OpportunitiesList.ListView
    When I open the revenuelineitems subpanel on #Opp_1Record view

    Then I verify fields for *RLI_1 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
      | fieldName       | value |
      | name            | QLI_1 |
      | discount_amount | $1.00 |
      | quantity        | -2.00 |

    Then I verify fields for *RLI_2 in #Opp_1Record.SubpanelsLayout.subpanels.revenuelineitems
      | fieldName       | value |
      | name            | QLI_2 |
      | discount_amount | 2.00% |
      | quantity        | 1.00  |


  @create_opportunity_from_quote @pr
  Scenario: Quotes > Record view > Create Opportunity from Quote while quote is in different currency
    # Create a quote
    Given Quotes records exist:
      | *name   | date_quote_expected_closed | quote_stage |
      | Quote_1 | 2018-10-19T19:20:22+00:00  | Negotiation |
    # Create Account
    Given Accounts records exist related via billing_accounts link to *Quote_1:
      | name  |
      | Acc_1 |
    # Create a product bundle
    Given ProductBundles records exist related via product_bundles link to *Quote_1:
      | *name   |
      | Group_1 |
    # Add QLI
    Given Products records exist related via products link:
      | *name | discount_price | discount_amount |
      | QLI_1 | 100            | 1               |
      | QLI_2 | 200            | 2               |
    Given I open about view and login
    # Add a new EUR currency
    When I go to "Currencies" url
    When I click Create button on #CurrenciesList header
    When I provide input for #CurrenciesDrawer.RecordView view
      | iso4217 | conversion_rate |
      | EUR     | 0.5             |
    When I click Save button on #CurrenciesDrawer header
    When I close alert
    When I choose Quotes in modules menu
    When I select *Quote_1 in #QuotesList.ListView
    Then I should see #Quote_1Record view
    When I click Edit button on #Quote_1Record header
    # Change the currency of the quote and save
    When I toggle Quote_Settings panel on #Quote_1Record.RecordView view
    When I provide input for #Quote_1Record.RecordView view
      | currency_id |
      | € (EUR)     |
    When I click Save button on #QuotesRecord header
    When I close alert
    When I toggle Quote_Settings panel on #Quote_1Record.RecordView view
    # Create Opportunity from Quote
    When I open actions menu in #Quote_1Record
    When I choose CreateOpportunity from actions menu in #Quote_1Record
    # The line below is a workaround for the bug
    When I Confirm confirmation alert
    Then I should see #OpportunitiesRecord view
    Then I verify fields on #OpportunitiesRecord.HeaderView
      | fieldName | value   |
      | name      | Quote_1 |
    When I click show more button on #OpportunitiesRecord view
    Then I verify fields on #OpportunitiesRecord.RecordView
      | fieldName        | value           |
      | date_closed      | 10/19/2018      |
      | account_name     | Acc_1           |
      | sales_status     | In Progress     |
      | amount           | €147.50 $295.00 |
      | best_case        | €147.50 $295.00 |
      | worst_case       | €147.50 $295.00 |
      | opportunity_type | New Business    |
