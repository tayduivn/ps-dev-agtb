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

  @list
  Scenario: Quotes > List View > Contain pre-created record
    Given Quotes records exist:
      | *name   | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country | date_quote_expected_closed |
      | Quote_1 | City 1               | Street address here    | 220051                     | WA                    | USA                     | 2017-10-19T19:20:22+00:00  |
    Given Accounts records exist related via billing_accounts link:
      | name  |
      | Acc_1 |
    Given I open about view and login
    When I choose Quotes in modules menu
    Then I should see *Quote_1 in #QuotesList.ListView
    Then I verify fields for *Quote_1 in #QuotesList.ListView
      | fieldName | value   |
      | name      | Quote_1 |

  @list-preview
  Scenario: Quotes > List View > Preview > Check fields
    Given Quotes records exist:
      | *name   | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country | date_quote_expected_closed |
      | Quote_1 | City 1               | Street address here    | 220051                     | WA                    | USA                     | 2017-10-19T19:20:22+00:00  |
    Given Accounts records exist related via billing_accounts link:
      | name  |
      | Acc_1 |
    Given I open about view and login
    When I choose Quotes in modules menu
    When I click on preview button on *Quote_1 in #QuotesList.ListView
    Then I should see #Quote_1Preview view
    Then I verify fields on #Quote_1Preview.PreviewView
      | fieldName                  | value               |
      | name                       | Quote_1             |
      | billing_address_street     | Street address here |
      | billing_address_postalcode | 220051              |
      | billing_address_state      | WA                  |
      | billing_address_country    | USA                 |

  @list-search
  Scenario: Quotes > List View > Filter > Search main input
    Given Quotes records exist:
      | *name   | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country | date_quote_expected_closed |
      | Quote_1 | City 1               | Street address here    | 220051                     | WA                    | USA                     | 2017-10-19T19:20:22+00:00  |
    Given Accounts records exist related via billing_accounts link:
      | name  |
      | Acc_1 |
    Given I open about view and login
    When I choose Quotes in modules menu
    Then I should see #QuotesList.ListView view
    When I search for "Quote_1" in #QuotesList.FilterView view
    Then I should see *Quote_1 in #QuotesList.ListView
    Then I verify fields for *Quote_1 in #QuotesList.ListView
      | fieldName | value   |
      | name      | Quote_1 |

  @delete
  Scenario: Quotes > Delete > Delete account from Record View
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
    When I Confirm confirmation alert
    Then I should see #QuotesList.ListView view
    Then I should not see *Quote_2 in #QuotesList.ListView

  @change_name
  Scenario: Quotes > Record View > Edit > Change Record Name > Save
    Given Quotes records exist:
      | *name   | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country | date_quote_expected_closed | quote_stage |
      | Quote_3 | City 1               | Street address here    | 220051                     | WA                    | USA                     | 2017-10-19T19:20:22+00:00  | Negotiation |
    Given Accounts records exist related via billing_accounts link:
      | name  |
      | Acc_1 |
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
    When I provide input for #Quote_3Record.RecordView view
      | quote_stage |
      | Delivered   |
    When I click Save button on #Quote_3Record header
    Then I verify fields on #Quote_3Record.HeaderView
      | fieldName | value    |
      | name      | Quote_34 |
    Then I verify fields on #Quote_3Record.RecordView
      | fieldName   | value     |
      | quote_stage | Delivered |

  @change_quote_stage
  Scenario: Quotes > Record View > Edit > Change Quote Stage > Save
    Given Quotes records exist:
      | *name   | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country | date_quote_expected_closed | quote_stage |
      | Quote_3 | City 1               | Street address here    | 220051                     | WA                    | USA                     | 2017-10-19T19:20:22+00:00  | Negotiation |
    Given Accounts records exist related via billing_accounts link:
      | name  |
      | Acc_1 |
    Given I open about view and login
    When I choose Quotes in modules menu
    When I select *Quote_3 in #QuotesList.ListView
    Then I should see #Quote_3Record view
    When I open actions menu in #Quote_3Record
    When I click Edit button on #Quote_3Record header
    Then I should see #Quote_3Record view
    When I provide input for #Quote_3Record.RecordView view
      | quote_stage |
      | Delivered   |
    When I click Save button on #Quote_3Record header
    Then I verify fields on #Quote_3Record.RecordView
      | fieldName   | value     |
      | quote_stage | Delivered |

  @change_quote_stage
  Scenario: Quotes > Record View > Toggle panels in edit/record view
    Given Quotes records exist:
      | *name   | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country | date_quote_expected_closed | quote_stage |
      | Quote_3 | City 1               | Street address here    | 220051                     | WA                    | USA                     | 2017-10-19T19:20:22+00:00  | Negotiation |
    Given Accounts records exist related via billing_accounts link:
      | name  |
      | Acc_1 |
    Given I open about view and login
    When I choose Quotes in modules menu
    When I select *Quote_3 in #QuotesList.ListView
    Then I should see #Quote_3Record view
    When I open actions menu in #Quote_3Record
    When I click Edit button on #Quote_3Record header
    When I toggle Business_Card panel on #Quote_3Record.RecordView view
    When I toggle Billing_and_Shipping panel on #Quote_3Record.RecordView view
    When I toggle Quote_Settings panel on #Quote_3Record.RecordView view
    When I toggle Show_More panel on #Quote_3Record.RecordView view
    When I toggle Business_Card panel on #Quote_3Record.RecordView view
    When I toggle Billing_and_Shipping panel on #Quote_3Record.RecordView view
    When I toggle Quote_Settings panel on #Quote_3Record.RecordView view
    When I toggle Show_More panel on #Quote_3Record.RecordView view
    When I click Cancel button on #Quote_3Record header
