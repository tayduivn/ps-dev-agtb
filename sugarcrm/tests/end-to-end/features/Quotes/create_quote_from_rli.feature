# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@quotes
Feature: Create Quote From RLI

  Background:
    Given I use default account
    Given I launch App

  @generate_quote_from_rli @T_34170
  Scenario: Quotes > Generate Quote from RLI > Cancel
    # Create RLI
    Given RevenueLineItems records exist:
      | *name | date_closed               | likely_case | best_case | sales_stage | quantity |
      | RLI_1 | 2018-10-19T19:20:22+00:00 | 3000        | 3000      | Prospecting | 3        |
    # Create Opportunity
    Given Opportunities records exist related via opportunities link:
      | name  |
      | Opp_1 |
    # Create Account. (the linking part does not work)
    Given Accounts records exist related via accounts link:
      | name  |
      | Acc_1 |
    Given Contacts records exist:
      | last_name | first_name | phone_home     |
      | Nisevich  | Alex       | (798) 852-5170 |
    Given I open about view and login
    When I choose RevenueLineItems in modules menu
    When I select *RLI_1 in #RevenueLineItemsList.ListView
    Then I should see #RLI_1Record view
    # Manually add account info to Opportunity
    When I click Edit button on #RLI_1Record header
    When I provide input for #RLI_1Record.RecordView view
      | opportunity_name |
      | Opp_1            |
    When I click Save button on #RLI_1Record header
    When I close alert
    # Generate Quote from RLI
    When I open actions menu in #RLI_1Record
    * I choose GenerateQuote from actions menu in #RLI_1Record
    Then I should see #QuotesRecord view
    When I toggle Billing_and_Shipping panel on #QuotesRecord.RecordView view
    #Provide input for the following fields and Save
    When I provide input for #QuotesRecord.RecordView view
      | *        | quote_stage | date_quote_expected_closed | payment_terms | purchase_order_num | billing_contact_name |
      | RecordID | Delivered   | 12/12/2018                 | Net 15        | 3940021            | Alex Nisevich        |
    When I click Cancel button on #QuotesRecord header
    When I Confirm confirmation alert
    # Verify that quote is not created
    Then I should see #RLI_1Record view
    Then I verify fields on #RLI_1Record.RecordView
      | fieldName  | value |
      | quote_name |       |


  @generate_quote_from_rli @T_34171
  Scenario: Quotes > Generate Quote from RLI > Save
    # Create RLI
    Given RevenueLineItems records exist:
      | *name | date_closed               | likely_case | best_case | sales_stage | quantity |
      | RLI_1 | 2018-10-19T19:20:22+00:00 | 3000        | 3000      | Prospecting | 3        |
    # Create Opportunity
    Given Opportunities records exist related via opportunities link:
      | name  |
      | Opp_1 |
    # Create Account. (the linking part does not work)
    Given Accounts records exist related via accounts link:
      | *name | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country |
      | Acc_1 | City 1               | Street address here    | 220051                     | WA                    | USA                     |
    Given Contacts records exist:
      | last_name | first_name | phone_home     |
      | Nisevich  | Alex       | (798) 852-5170 |
    Given I open about view and login
    When I choose RevenueLineItems in modules menu
    When I select *RLI_1 in #RevenueLineItemsList.ListView
    Then I should see #RLI_1Record view
    # Manually add account info to Opportunity
    When I click Edit button on #RLI_1Record header
    When I provide input for #RLI_1Record.RecordView view
      | opportunity_name |
      | Opp_1            |
    When I click Save button on #RLI_1Record header
    When I close alert
    # Generate Quote from RLI
    When I open actions menu in #RLI_1Record
    * I choose GenerateQuote from actions menu in #RLI_1Record
    Then I should see #QuotesRecord view
    When I toggle Billing_and_Shipping panel on #QuotesRecord.RecordView view
    #Provide input for the following fields and Save
    When I provide input for #QuotesRecord.RecordView view
      | *        | quote_stage | date_quote_expected_closed | payment_terms | purchase_order_num | billing_contact_name |
      | RecordID | Delivered   | 12/12/2018                 | Net 15        | 3940021            | Alex Nisevich        |
    When I click Save button on #QuotesRecord header
    When I close alert
    # Verify that quote is saved successfully
    When I toggle Billing_and_Shipping panel on #RecordIDRecord.RecordView view
    Then I verify fields on #RecordIDRecord.HeaderView
      | fieldName | value |
      | name      | RLI_1 |
    Then I verify fields on #RecordIDRecord.RecordView
      | fieldName                  | value               |
      | quote_stage                | Delivered           |
      | opportunity_name           | Opp_1               |
      | billing_account_name       | Acc_1               |
      | date_quote_expected_closed | 12/12/2018          |
      | payment_terms              | Net 15              |
      | purchase_order_num         | 3940021             |
      | billing_contact_name       | Alex Nisevich       |
      | billing_address_city       | City 1              |
      | billing_address_street     | Street address here |
      | billing_address_postalcode | 220051              |
      | billing_address_state      | WA                  |
      | billing_address_country    | USA                 |
    # Add verification of QLi table numbers after AT-78 is implemented


  @generate_quote_from_rli @T_34173
  Scenario: Quotes > Generate Quote from RLI > RLI has a link to generated quote
    # Create RLI
    Given RevenueLineItems records exist:
      | *name | date_closed               | likely_case | best_case | sales_stage | quantity |
      | RLI_1 | 2018-10-19T19:20:22+00:00 | 3000        | 3000      | Prospecting | 3        |
    # Create Opportunity
    Given Opportunities records exist related via opportunities link:
      | name  |
      | Opp_1 |
    # Create Account. (the linking part does not work)
    Given Accounts records exist related via accounts link:
      | *name |
      | Acc_1 |
    Given I open about view and login
    When I choose RevenueLineItems in modules menu
    When I select *RLI_1 in #RevenueLineItemsList.ListView
    Then I should see #RLI_1Record view
    # Manually add account info to Opportunity
    When I click Edit button on #RLI_1Record header
    When I provide input for #RLI_1Record.RecordView view
      | opportunity_name |
      | Opp_1            |
    When I click Save button on #RLI_1Record header
    When I close alert
    # Generate Quote from RLI
    When I open actions menu in #RLI_1Record and check:
      | menu_item     | active |
      | GenerateQuote | true   |
    * I choose GenerateQuote from actions menu in #RLI_1Record
    Then I should see #QuotesRecord view
    When I toggle Billing_and_Shipping panel on #QuotesRecord.RecordView view
    #Provide input for the following fields and Save
    When I provide input for #QuotesRecord.HeaderView view
      | *        | name             |
      | RecordID | Quote from RLI_1 |
    When I provide input for #QuotesRecord.RecordView view
      | *        | date_quote_expected_closed |
      | RecordID | 12/12/2018                 |
    When I click Save button on #QuotesRecord header
    # Verify that RLI has a link to generated quote
    When I choose RevenueLineItems in modules menu
    When I select *RLI_1 in #RevenueLineItemsList.ListView
    Then I should see #RLI_1Record view
    When I open actions menu in #RLI_1Record and check:
      | menu_item     | active |
      | GenerateQuote | false  |
    Then I verify fields on #RLI_1Record.RecordView
      | fieldName  | value            |
      | quote_name | Quote from RLI_1 |
    When I click quote_name field on #RLI_1Record.RecordView view
    Then I should see #RecordIDRecord view

