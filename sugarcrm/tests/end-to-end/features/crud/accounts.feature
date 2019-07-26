# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@crud_modules_accounts @job1
Feature: Accounts module verification

  Background:
    Given I use default account
    Given I launch App with config: "skipTutorial"

  @list
  Scenario: Accounts > List View > Contain pre-created record
    Given Accounts records exist:
      | *name     | website        | industry | account_type | service_level | phone_office | phone_alternate | email       | phone_fax    | tag  | twitter | description | sic_code | ticker_symbol | annual_revenue | employees | ownership | rating | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country |
      | Account_A | www.google.com | Apparel  | Analyst      | T1            | 555-555-0000 | 555-555-0001    | bob@bob.com | 555-555-0002 | tags | twitter | description | siccode  | tic           | 5000000        | 2         | Gates     | 0      | City 1               | Street address here    | 220051                     | WA                    | USA                     |
    Given I open about view and login
    When I choose Accounts in modules menu
    Then I should see *Account_A in #AccountsList.ListView
    Then I verify fields for *Account_A in #AccountsList.ListView
      | fieldName | value     |
      | name      | Account_A |

  @list-preview
  Scenario: Accounts > List View > Preview > Check fields
    Given Accounts records exist:
      | *name     | website        | industry | account_type | service_level | phone_office | phone_alternate | email       | phone_fax    | tag  | twitter | description | sic_code | ticker_symbol | annual_revenue | employees | ownership | rating | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country |
      | Account_A | www.google.com | Apparel  | Analyst      | T1            | 555-555-0000 | 555-555-0001    | bob@bob.com | 555-555-0002 | tags | twitter | description | siccode  | tic           | 5000000        | 2         | Gates     | 0      | City 1               | Street address here    | 220051                     | WA                    | USA                     |
    Given I open about view and login
    When I choose Accounts in modules menu
    When I click on preview button on *Account_A in #AccountsList.ListView
    Then I should see #Account_APreview view
    Then I verify fields on #Account_APreview.PreviewView
      | fieldName | value     |
      | name      | Account_A |


  @list-search
  Scenario: Accounts > List View > Filter > Search main input
    Given Accounts records exist:
      | *name          | website        | industry | account_type | service_level | phone_office | phone_alternate | email       | phone_fax    | tag  | twitter | description | sic_code | ticker_symbol | annual_revenue | employees | ownership | rating | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country |
      | Account_Search | www.google.com | Apparel  | Analyst      | T1            | 555-555-0000 | 555-555-0001    | bob@bob.com | 555-555-0002 | tags | twitter | description | siccode  | tic           | 5000000        | 2         | Gates     | 0      | City 1               | Street address here    | 220051                     | WA                    | USA                     |
    Given I open about view and login
    When I choose Accounts in modules menu
    Then I should see #AccountsList.ListView view
    When I search for "Account_Search" in #AccountsList.FilterView view
    Then I should see *Account_Search in #AccountsList.ListView
    Then I verify fields for *Account_Search in #AccountsList.ListView
      | fieldName | value          |
      | name      | Account_Search |

  @delete
  Scenario: Accounts > Delete > Delete account from Record View
    Given Accounts records exist:
      | *name     | website        | industry | account_type | service_level | phone_office | phone_alternate | email       | phone_fax    | tag  | twitter | description | sic_code | ticker_symbol | annual_revenue | employees | ownership | rating | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country |
      | Account_A | www.google.com | Apparel  | Analyst      | T1            | 555-555-0000 | 555-555-0001    | bob@bob.com | 555-555-0002 | tags | twitter | description | siccode  | tic           | 5000000        | 2         | Gates     | 0      | City 1               | Street address here    | 220051                     | WA                    | USA                     |
    Given I open about view and login
    When I choose Accounts in modules menu
    When I select *Account_A in #AccountsList.ListView
    Then I should see #Account_ARecord view
    When I open actions menu in #Account_ARecord
    * I choose Delete from actions menu in #Account_ARecord
    When I Confirm confirmation alert
    Then I should see #AccountsList.ListView view
    Then I should not see *Account_A in #AccountsList.ListView

  @preview-edit-cancel-save
  Scenario Outline: Accounts > Preview > Preview Edit > Save/Cancel
    Given Accounts records exist:
      | *name     | website        | industry | account_type | service_level | phone_office | phone_alternate | email       | phone_fax    | tag  | twitter | description | sic_code | ticker_symbol | annual_revenue | employees | ownership | rating | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country |
      | Account_A | www.google.com | Apparel  | Analyst      | T1            | 555-555-0000 | 555-555-0001    | bob@bob.com | 555-555-0002 | tags | twitter | description | sic      | tic           | 5000000        | 2         | Gates     | 0      | City 1               | Street address here    | 220051                     | WA                    | USA                     |

    Given I open about view and login
    When I choose Accounts in modules menu

    # preview verification
    When I click on preview button on *Account_A in #AccountsList.ListView
    Then I should see #Account_APreview view
    Then I click show more button on #Account_APreview view
    Then I verify fields on #Account_APreview.PreviewView
      | fieldName                  | value                        |
      | name                       | <name>                       |
      | website                    | <http://><website1>          |
      | industry                   | <industry>                   |
      | account_type               | <account_type>               |
      | service_level              | <service_level>              |
      | phone_office               | <phone_office>               |
      | phone_alternate            | <phone_alternate>            |
      | email                      | <email>                      |
      | phone_fax                  | <phone_fax>                  |
      | twitter                    | <twitter>                    |
      | description                | <description>                |
      | sic_code                   | <sic_code>                   |
      | ticker_symbol              | <ticker_symbol>              |
      | annual_revenue             | <annual_revenue>             |
      | employees                  | <employees>                  |
      | ownership                  | <ownership>                  |
      | rating                     | <rating>                     |
      | billing_address_city       | <billing_address_city>       |
      | billing_address_street     | <billing_address_street>     |
      | billing_address_postalcode | <billing_address_postalcode> |
      | billing_address_state      | <billing_address_state>      |
      | billing_address_country    | <billing_address_country>    |

    # preview edit, preview for cancel
    When I click on Edit button in #Account_APreview.PreviewHeaderView
    When I provide input for #Account_APreview.PreviewView view
      | name   | website    | industry   | account_type   | service_level   | phone_office   | phone_alternate   | email   | phone_fax   | twitter   | description   | sic_code   | ticker_symbol   | annual_revenue   | employees   | ownership   | rating   | billing_address_city   | billing_address_street   | billing_address_postalcode   | billing_address_state   | billing_address_country   |
      | <name> | <website2> | <industry> | <account_type> | <service_level> | <phone_office> | <phone_alternate> | <email> | <phone_fax> | <twitter> | <description> | <sic_code> | <ticker_symbol> | <annual_revenue> | <employees> | <ownership> | <rating> | <billing_address_city> | <billing_address_street> | <billing_address_postalcode> | <billing_address_state> | <billing_address_country> |

    When I click on Cancel button in #Account_APreview.PreviewHeaderView

    # verify preview cancel
    Then I verify fields on #Account_APreview.PreviewView
      | fieldName                  | value                        |
      | name                       | <name>                       |
      | website                    | <http://><website1>          |
      | industry                   | <industry>                   |
      | account_type               | <account_type>               |
      | service_level              | <service_level>              |
      | phone_office               | <phone_office>               |
      | phone_alternate            | <phone_alternate>            |
      | email                      | <email>                      |
      | phone_fax                  | <phone_fax>                  |
      | twitter                    | <twitter>                    |
      | description                | <description>                |
      | sic_code                   | <sic_code>                   |
      | ticker_symbol              | <ticker_symbol>              |
      | annual_revenue             | <annual_revenue>             |
      | employees                  | <employees>                  |
      | ownership                  | <ownership>                  |
      | rating                     | <rating>                     |
      | billing_address_city       | <billing_address_city>       |
      | billing_address_street     | <billing_address_street>     |
      | billing_address_postalcode | <billing_address_postalcode> |
      | billing_address_state      | <billing_address_state>      |
      | billing_address_country    | <billing_address_country>    |

    # preview edit, preview for save
    When I click on Edit button in #Account_APreview.PreviewHeaderView
    When I provide input for #Account_APreview.PreviewView view
      | name   | website    | industry   | account_type   | service_level   | phone_office   | phone_alternate   | email   | phone_fax   | twitter   | description   | sic_code   | ticker_symbol   | annual_revenue   | employees   | ownership   | rating   | billing_address_city   | billing_address_street   | billing_address_postalcode   | billing_address_state   | billing_address_country   |
      | <name> | <website2> | <industry> | <account_type> | <service_level> | <phone_office> | <phone_alternate> | <email> | <phone_fax> | <twitter> | <description> | <sic_code> | <ticker_symbol> | <annual_revenue> | <employees> | <ownership> | <rating> | <billing_address_city> | <billing_address_street> | <billing_address_postalcode> | <billing_address_state> | <billing_address_country> |

    When I click on Save button in #Account_APreview.PreviewHeaderView
    When I close alert

    # verify preview save
    Then I verify fields on #Account_APreview.PreviewView
      | fieldName                  | value                        |
      | name                       | <name>                       |
      | website                    | <http://><website2>          |
      | industry                   | <industry>                   |
      | account_type               | <account_type>               |
      | service_level              | <service_level>              |
      | phone_office               | <phone_office>               |
      | phone_alternate            | <phone_alternate>            |
      | email                      | <email>                      |
      | phone_fax                  | <phone_fax>                  |
      | twitter                    | <twitter>                    |
      | description                | <description>                |
      | sic_code                   | <sic_code>                   |
      | ticker_symbol              | <ticker_symbol>              |
      | annual_revenue             | <annual_revenue>             |
      | employees                  | <employees>                  |
      | ownership                  | <ownership>                  |
      | rating                     | <rating>                     |
      | billing_address_city       | <billing_address_city>       |
      | billing_address_street     | <billing_address_street>     |
      | billing_address_postalcode | <billing_address_postalcode> |
      | billing_address_state      | <billing_address_state>      |
      | billing_address_country    | <billing_address_country>    |

    Examples:
      | name      | http:// | website1       | website2      | industry | account_type | service_level | phone_office | phone_alternate | email       | phone_fax    | twitter | description | sic_code | ticker_symbol | annual_revenue | employees | ownership | rating | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country |
      | Account_A | http:// | www.google.com | www.yahoo.com | Apparel  | Analyst      | Tier 1        | 555-555-0000 | 555-555-0001    | bob@bob.com | 555-555-0002 | twitter | description | sic      | tic           | 5000000        | 2         | Gates     | 0      | City 1               | Street address here    | 220051                     | WA                    | USA                     |
