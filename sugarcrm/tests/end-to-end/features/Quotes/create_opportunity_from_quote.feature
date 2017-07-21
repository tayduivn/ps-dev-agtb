# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@modules @quotes-group
Feature: Create Opportunity from Quote

  Background:
    Given I use default account
    Given I launch App

  @create_opportunity_from_quote @T_33332
  Scenario: Quotes > Record view > Create Opportunity from Quote
    # Create Account
    Given Accounts records exist:
      | name  | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country |
      | Acc_1 | City 1               | Street address here    | 220051                     | WA                    | USA                     |
    # Create a quote
    Given Quotes records exist:
      | *name   | date_quote_expected_closed | quote_stage |
      | Quote_1 | 2018-10-19T19:20:22+00:00  | Negotiation |
    # Create a product bundle
    Given ProductBundles records exist related via product_bundles link:
      | *name   |
      | Group_1 |
    # Add QLI
    Given Products records exist related via products link:
      | *name | discount_price | discount_amount |
      | QLI_1 | 100            | 1               |
      | QLI_2 | 200            | 2               |
    Given I open about view and login

    # Link Account to Quote
    When I choose Quotes in modules menu
    When I select *Quote_1 in #QuotesList.ListView
    Then I should see #Quote_1Record view
    When I click Edit button on #Quote_1Record header
    When I toggle Billing_and_Shipping panel on #Quote_1Record.RecordView view
    When I provide input for #QuotesRecord.RecordView view
      | billing_account_name |
      | Acc_1                |
    When I Confirm confirmation alert
    When I click Save button on #Quote_1Record header
    When I close alert

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
      | amount           | $295.00      |
      | best_case        | $295.00      |
      | worst_case       | $295.00      |
      | opportunity_type | New Business |





