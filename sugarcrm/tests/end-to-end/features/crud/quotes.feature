# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@crud @modules @quotes-group
Feature: Quotes module verification

  Background:
    Given I use default account
    Given I launch App

  @list-preview @T_32765
  Scenario Outline: Quotes > List View > Preview
    Given Quotes records exist:
      | *name   | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country | date_quote_expected_closed |
      | Quote_1 | City 1               | Street address here    | 220051                     | <state>               | USA                     | 2020-10-19T19:20:22+00:00  |
    Given Accounts records exist related via billing_accounts link:
      | name  |
      | Acc_1 |
    Given I open about view and login
    When I choose Quotes in modules menu
    Then I should see *Quote_1 in #QuotesList.ListView
    Then I verify fields for *Quote_1 in #QuotesList.ListView
      | fieldName | value   |
      | name      | Quote_1 |
    When I click on preview button on *Quote_1 in #QuotesList.ListView
    Then I should see #Quote_1Preview view
    Then I verify fields on #Quote_1Preview.PreviewView
      | fieldName                  | value               |
      | name                       | Quote_1             |
      | billing_address_street     | Street address here |
      | billing_address_postalcode | 220051              |
      | billing_address_state      | <state>             |
      | billing_address_country    | USA                 |
      | date_quote_expected_closed | 10/19/2020          |
    Examples:
      | state |
      | CA    |
      | WA    |

  @list-search @T_34376
  Scenario: Quotes > List View > Filter > Search main input
    Given Quotes records exist:
      | *name   | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country | date_quote_expected_closed |
      | Quote_1 | City 1               | Street address here  1 | 120051                     | WA                    | USA                     | 2020-10-19T19:20:22+00:00  |
      | Quote_2 | City 2               | Street address here  2 | 220051                     | CA                    | USA                     | 2020-11-19T19:20:22+00:00  |
      | Quote_3 | City 3               | Street address here  3 | 320051                     | NC                    | USA                     | 2020-12-19T19:20:22+00:00  |
    Given I open about view and login
    When I choose Quotes in modules menu
    Then I should see #QuotesList.ListView view
    When I search for "Quote_2" in #QuotesList.FilterView view
    Then I should not see *Quote_1 in #QuotesList.ListView
    Then I should see *Quote_2 in #QuotesList.ListView
    Then I should not see *Quote_3 in #QuotesList.ListView
    Then I verify fields for *Quote_2 in #QuotesList.ListView
      | fieldName                  | value      |
      | name                       | Quote_2    |
      | date_quote_expected_closed | 11/19/2020 |

  @delete @T_34377
  Scenario: Quotes > Record View > Delete > Cancel/Confirm
    Given Quotes records exist:
      | *name   | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country | date_quote_expected_closed |
      | Quote_2 | City 1               | Street address here    | 220051                     | WA                    | USA                     | 2017-10-19T19:20:22+00:00  |
    Given Accounts records exist related via billing_accounts link:
      | name  |
      | Acc_1 |
    Given I open about view and login
    When I choose Quotes in modules menu
    When I select *Quote_2 in #QuotesList.ListView
    Then I should see #Quote_2Record view
    When I open actions menu in #Quote_2Record
    * I choose Delete from actions menu in #Quote_2Record
    # Cancel record deletion
    When I Cancel confirmation alert
    Then I should see #Quote_2Record view
    Then I verify fields on #Quote_2Record.HeaderView
      | fieldName | value   |
      | name      | Quote_2 |
    When I open actions menu in #Quote_2Record
    * I choose Delete from actions menu in #Quote_2Record
    # Confirm record deletion
    When I Confirm confirmation alert
    Then I should see #QuotesList.ListView view
    Then I should not see *Quote_2 in #QuotesList.ListView

  @edit-cancel @T_34378
  Scenario: Quotes > Record View > Edit > Cancel
    Given Quotes records exist:
      | *name   | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country | date_quote_expected_closed | quote_stage |
      | Quote_3 | City 1               | Street address here    | 220051                     | WA                    | USA                     | 2017-10-19T19:20:22+00:00  | Negotiation |
    Given Accounts records exist related via billing_accounts link:
      | name  |
      | Acc_1 |
    Given Accounts records exist:
      | name  |
      | Acc_2 |
    Given I open about view and login
    When I choose Quotes in modules menu
    When I select *Quote_3 in #QuotesList.ListView
    Then I should see #Quote_3Record view
    When I open actions menu in #Quote_3Record
    When I click Edit button on #Quote_3Record header
    Then I should see #Quote_3Record view
    When I provide input for #Quote_3Record.HeaderView view
      | name     |
      | Quote_34 |
    When I toggle Billing_and_Shipping panel on #Quote_3Record.RecordView view
    When I provide input for #Quote_3Record.RecordView view
      | quote_stage | date_quote_expected_closed | billing_account_name |
      | Delivered   | 12/12/2020                 | Acc_2                |
    When I Confirm confirmation alert
    When I click Cancel button on #Quote_3Record header
    Then I verify fields on #Quote_3Record.HeaderView
      | fieldName | value   |
      | name      | Quote_3 |
    Then I verify fields on #Quote_3Record.RecordView
      | fieldName                  | value       |
      | quote_stage                | Negotiation |
      | date_quote_expected_closed | 10/19/2017  |
      | billing_account_name       | Acc_1       |

  @edit-save @T_34378
  Scenario: Quotes > Record View > Edit > Save
    Given Quotes records exist:
      | *name   | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country | date_quote_expected_closed | quote_stage |
      | Quote_3 | City 1               | Street address here    | 220051                     | WA                    | USA                     | 2017-10-19T19:20:22+00:00  | Negotiation |
    Given Accounts records exist related via billing_accounts link:
      | name  |
      | Acc_1 |
    Given Accounts records exist:
      | name  |
      | Acc_2 |
    Given I open about view and login
    When I choose Quotes in modules menu
    When I select *Quote_3 in #QuotesList.ListView
    Then I should see #Quote_3Record view
    When I open actions menu in #Quote_3Record
    When I click Edit button on #Quote_3Record header
    Then I should see #Quote_3Record view
    When I provide input for #Quote_3Record.HeaderView view
      | name     |
      | Quote_34 |
    When I toggle Billing_and_Shipping panel on #Quote_3Record.RecordView view
    When I provide input for #Quote_3Record.RecordView view
      | quote_stage | date_quote_expected_closed | billing_account_name |
      | Delivered   | 12/12/2020                 | Acc_2                |
    When I Confirm confirmation alert
    When I click Save button on #Quote_3Record header
    Then I verify fields on #Quote_3Record.HeaderView
      | fieldName | value    |
      | name      | Quote_34 |
    Then I verify fields on #Quote_3Record.RecordView
      | fieldName                  | value      |
      | quote_stage                | Delivered  |
      | date_quote_expected_closed | 12/12/2020 |
      | billing_account_name       | Acc_2      |


  @create_cancel_save @T_34379 @ci-excluded
  Scenario: Quotes > Create > Cancel/Save
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
      | *        | date_quote_expected_closed | billing_account_name |
      | RecordID | 12/12/2017                 | myAccount            |
    When I Confirm confirmation alert
    # Cancel quote record creation
    When I click Cancel button on #QuotesRecord header
    # TODO: Remove next line after sfa-5069 is fixed
    When I Confirm confirmation alert
    When I click Create button on #QuotesList header
    When I toggle Billing_and_Shipping panel on #QuotesRecord.RecordView view
    When I provide input for #QuotesRecord.HeaderView view
      | *        | name  |
      | RecordID | Alex2 |
    When I provide input for #QuotesRecord.RecordView view
      | *        | date_quote_expected_closed | billing_account_name |
      | RecordID | 12/12/2017                 | myAccount            |
    When I Confirm confirmation alert
    When I click Save button on #QuotesRecord header
    When I toggle Billing_and_Shipping panel on #RecordIDRecord.RecordView view
    Then I verify fields on #RecordIDRecord.RecordView
      | fieldName                   | value               |
      | date_quote_expected_closed  | 12/12/2017          |
      | billing_account_name        | myAccount           |
      | billing_address_city        | City 1              |
      | billing_address_street      | Street address here |
      | billing_address_postalcode  | 220051              |
      | billing_address_state       | WA                  |
      | billing_address_country     | USA                 |
      | shipping_account_name       | myAccount           |
      | shipping_address_city       | City 1              |
      | shipping_address_street     | Street address here |
      | shipping_address_postalcode | 220051              |
      | shipping_address_state      | WA                  |
      | shipping_address_country    | USA                 |
