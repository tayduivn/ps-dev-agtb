# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@modules @quotes-group
Feature: Billing/Shipping Account verification

  Background:
    Given I use default account
    Given I launch App

  @billing_and_shipping_address @T_34380 @ci-excluded
  Scenario: Quotes > Create -> Verify billing address is correct
    Given Accounts records exist:
      | *name     | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country |
      | myAccount | City 1               | Street address here    | 220051                     | WA                    | USA                     |
    Given I open about view and login
    When I choose Quotes in modules menu
    When I click Create button on #QuotesList header
    When I toggle Billing_and_Shipping panel on #QuotesRecord.RecordView view
    When I provide input for #QuotesRecord.HeaderView view
      | *        | name  |
      | RecordID | Alex2 |
    When I provide input for #QuotesRecord.RecordView view
      | *        | copy  |
      | RecordID | false |
    When I provide input for #QuotesRecord.RecordView view
      | *        | date_quote_expected_closed | billing_account_name |
      | RecordID | 12/12/2017                 | myAccount            |
    When I Confirm confirmation alert
    Then I verify fields on #QuotesRecord.RecordView
      | fieldName                  | value               |
      | billing_account_name       | myAccount           |
      | billing_address_city       | City 1              |
      | billing_address_street     | Street address here |
      | billing_address_postalcode | 220051              |
      | billing_address_state      | WA                  |
      | billing_address_country    | USA                 |
    When I click Save button on #QuotesRecord header
    When I toggle Billing_and_Shipping panel on #RecordIDRecord.RecordView view
    Then I verify fields on #RecordIDRecord.RecordView
      | fieldName                  | value               |
      | date_quote_expected_closed | 12/12/2017          |
      | billing_account_name       | myAccount           |
      | billing_address_city       | City 1              |
      | billing_address_street     | Street address here |
      | billing_address_postalcode | 220051              |
      | billing_address_state      | WA                  |
      | billing_address_country    | USA                 |
      | shipping_account_name      | myAccount           |


  @create @T_34381 @ci-excluded
  Scenario: Quotes > Create -> Verify billing and shipping addresses are correct
    Given Accounts records exist:
      | *name           | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country |
      | Billing Account | City 1               | Street address here    | 220051                     | WA                    | USA                     |
    Given Accounts records exist:
      | *name            | shipping_address_city | shipping_address_street | shipping_address_postalcode | shipping_address_state | shipping_address_country |
      | Shipping Account | San Jose              | 10050 N Wolfe Rd        | 95014                       | CA                     | USA                      |
    Given I open about view and login
    When I choose Quotes in modules menu
    When I click Create button on #QuotesList header
    When I toggle Billing_and_Shipping panel on #QuotesRecord.RecordView view
    When I provide input for #QuotesRecord.HeaderView view
      | *        | name  |
      | RecordID | Alex2 |
    When I provide input for #QuotesRecord.RecordView view
      | *        | copy  | date_quote_expected_closed | billing_account_name |
      | RecordID | false | 12/12/2017                 | Billing Account      |
    When I Confirm confirmation alert
    When I provide input for #QuotesRecord.RecordView view
      | *        | shipping_account_name |
      | RecordID | Shipping Account      |
    When I Confirm confirmation alert
    Then I verify fields on #QuotesRecord.RecordView
      | fieldName                   | value               |
      | billing_account_name        | Billing Account     |
      | billing_address_city        | City 1              |
      | billing_address_street      | Street address here |
      | billing_address_postalcode  | 220051              |
      | billing_address_state       | WA                  |
      | billing_address_country     | USA                 |
      | shipping_account_name       | Shipping Account    |
      | shipping_address_city       | San Jose            |
      | shipping_address_street     | 10050 N Wolfe Rd    |
      | shipping_address_postalcode | 95014               |
      | shipping_address_state      | CA                  |
      | shipping_address_country    | USA                 |
    When I click Save button on #QuotesRecord header
    When I toggle Billing_and_Shipping panel on #RecordIDRecord.RecordView view
    Then I verify fields on #RecordIDRecord.RecordView
      | fieldName                   | value               |
      | date_quote_expected_closed  | 12/12/2017          |
      | billing_account_name        | Billing Account     |
      | billing_address_city        | City 1              |
      | billing_address_street      | Street address here |
      | billing_address_postalcode  | 220051              |
      | billing_address_state       | WA                  |
      | billing_address_country     | USA                 |
      | shipping_account_name       | Shipping Account    |
      | shipping_address_city       | San Jose            |
      | shipping_address_street     | 10050 N Wolfe Rd    |
      | shipping_address_postalcode | 95014               |
      | shipping_address_state      | CA                  |
      | shipping_address_country    | USA                 |

