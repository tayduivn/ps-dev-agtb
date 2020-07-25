# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@crud_modules_accounts @job4
Feature: Accounts module verification

  Background:
    Given I use default account
    Given I launch App with config: "skipTutorial"

  @list
  Scenario Outline: Accounts > List View > Contain pre-created record
    Given Accounts records exist:
      | *name     | website        | industry | account_type | service_level | phone_office | phone_alternate | email       | phone_fax    | tag  | twitter | description | sic_code | ticker_symbol | annual_revenue | employees | ownership | rating | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country |
      | Account_A | www.google.com | Apparel  | Analyst      | T1            | 555-555-0000 | 555-555-0001    | bob@bob.com | 555-555-0002 | tags | twitter | description | siccode  | tic           | 5000000        | 2         | Gates     | 0      | City 1               | Street address here    | 220051                     | WA                    | USA                     |
    Given I open about view and login
    When I choose Accounts in modules menu
    Then I should see *Account_A in #AccountsList.ListView
    Then I verify fields for *Account_A in #AccountsList.ListView
      | fieldName               | value                     |
      | name                    | <name>                    |
      | phone_office            | <phone_office>            |
      | email                   | <email>                   |
      | billing_address_city    | <billing_address_city>    |
      | billing_address_country | <billing_address_country> |

    Examples:
      | name      | phone_office | email       | billing_address_city | billing_address_country |
      | Account_A | 555-555-0000 | bob@bob.com | City 1               | USA                     |

  @list-preview
  Scenario Outline: Accounts > List View > Preview > Check fields
    Given Accounts records exist:
      | *name     | website        | industry | account_type | service_level | phone_office | phone_alternate | email       | phone_fax    | tag  | twitter | description | sic_code | ticker_symbol | annual_revenue | employees | ownership | rating | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country |
      | Account_A | www.google.com | Apparel  | Analyst      | T1            | 555-555-0000 | 555-555-0001    | bob@bob.com | 555-555-0002 | tags | twitter | description | siccode  | tic           | 5000000        | 2         | Gates     | 0      | City 1               | Street address here    | 220051                     | WA                    | USA                     |
    Given I open about view and login
    When I choose Accounts in modules menu
    When I click on preview button on *Account_A in #AccountsList.ListView
    Then I should see #Account_APreview view
    Then I click show more button on #Account_APreview view
    Then I verify fields on #Account_APreview.PreviewView
      | fieldName                  | value                        |
      | name                       | <name>                       |
      | website                    | <http://><website1>          |
      | industry                   | <industry>                   |
      | account_type               | <account_type>               |
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
      | name      | http:// | website1       | industry | account_type | phone_office | phone_alternate | email       | phone_fax    | twitter | description | sic_code | ticker_symbol | annual_revenue | employees | ownership | rating | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country |
      | Account_A | http:// | www.google.com | Apparel  | Analyst      | 555-555-0000 | 555-555-0001    | bob@bob.com | 555-555-0002 | twitter | description | siccode  | tic           | 5000000        | 2         | Gates     | 0      | City 1               | Street address here    | 220051                     | WA                    | USA                     |


  @list-search
  Scenario Outline: Accounts > List View > Filter > Search main input
    Given Accounts records exist:
      | *name     | website        | industry | account_type | service_level | phone_office | phone_alternate | email       | phone_fax    | tag  | twitter | description | sic_code | ticker_symbol | annual_revenue | employees | ownership | rating | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country |
      | Account_A | www.google.com | Apparel  | Analyst      | T1            | 555-555-0000 | 555-555-0001    | bob@bob.com | 555-555-0002 | tags | twitter | description | siccode  | tic           | 5000000        | 2         | Gates     | 0      | City 1               | Street address here    | 220051                     | WA                    | USA                     |
    Given I open about view and login
    When I choose Accounts in modules menu
    Then I should see #AccountsList.ListView view
    When I search for "Account_A" in #AccountsList.FilterView view
    Then I should see *Account_A in #AccountsList.ListView
    Then I verify fields for *Account_A in #AccountsList.ListView
      | fieldName               | value                     |
      | name                    | <name>                    |
      | phone_office            | <phone_office>            |
      | email                   | <email>                   |
      | billing_address_city    | <billing_address_city>    |
      | billing_address_country | <billing_address_country> |

    Examples:
      | name      | phone_office | email       | billing_address_city | billing_address_country |
      | Account_A | 555-555-0000 | bob@bob.com | City 1               | USA                     |

  @list-edit-cancel-edit-save
  Scenario Outline: Accounts > List View > Edit > Cancel > Edit > Save
    Given Accounts records exist:
      | *name     | website        | industry | account_type | service_level | phone_office | phone_alternate | email       | phone_fax    | tag  | twitter | description | sic_code | ticker_symbol | annual_revenue | employees | ownership | rating | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country |
      | Account_A | www.google.com | Apparel  | Analyst      | T1            | 555-555-0000 | 555-555-0001    | bob@bob.com | 555-555-0002 | tags | twitter | description | siccode  | tic           | 5000000        | 2         | Gates     | 0      | City 1               | Street address here    | 220051                     | WA                    | USA                     |
    Given I open about view and login
    When I choose Accounts in modules menu
    Then I should see *Account_A in #AccountsList.ListView
    Then I verify fields for *Account_A in #AccountsList.ListView
      | fieldName               | value                     |
      | name                    | <name>                    |
      | billing_address_city    | <billing_address_city>    |
      | billing_address_country | <billing_address_country> |
      | phone_office            | <phone_office>            |
      | email                   | <email>                   |

    # edit/cancel in list view
    When I click ToggleSidePanel button on #Account_ARecord header
    When I click on Edit button for *Account_A in #AccountsList.ListView
    When I set values for *Account_A in #AccountsList.ListView
      | fieldName               | value                      |
      | name                    | <name2>                    |
      | billing_address_city    | <billing_address_city2>    |
      | billing_address_country | <billing_address_country2> |
      | phone_office            | <phone_office2>            |
      | email                   | <email2>                   |
    When I click on Cancel button for *Account_A in #AccountsList.ListView
    Then I verify fields for *Account_A in #AccountsList.ListView
      | fieldName               | value                     |
      | name                    | <name>                    |
      | billing_address_city    | <billing_address_city>    |
      | billing_address_country | <billing_address_country> |
      | phone_office            | <phone_office>            |
      | email                   | <email>                   |

    # edit/save in list view
    When I click on Edit button for *Account_A in #AccountsList.ListView
    When I set values for *Account_A in #AccountsList.ListView
      | fieldName               | value                      |
      | name                    | <name2>                    |
      | phone_office            | <phone_office2>            |
      | email                   | <email2>                   |
      | billing_address_city    | <billing_address_city2>    |
      | billing_address_country | <billing_address_country2> |
    When I click on Save button for *Account_A in #AccountsList.ListView
    Then I verify fields for *Account_A in #AccountsList.ListView
      | fieldName               | value                      |
      | name                    | <name2>                    |
      | phone_office            | <phone_office2>            |
      | email                   | <email2>                   |
      | billing_address_city    | <billing_address_city2>    |
      | billing_address_country | <billing_address_country2> |

    Examples:
      | name      | name2     | phone_office | phone_office2 | email       | email2            | billing_address_city | billing_address_city2 | billing_address_country | billing_address_country2 |
      | Account_A | Account_B | 555-555-0000 | 666-666-0000  | bob@bob.com | bobber@bobber.com | City 1               | City 2                | USA                     | UK                       |

  @list-delete
  Scenario Outline: Accounts > List View > Delete
    Given Accounts records exist:
      | *name     | website        | industry | account_type | service_level | phone_office | phone_alternate | email       | phone_fax    | tag  | twitter | description | sic_code | ticker_symbol | annual_revenue | employees | ownership | rating | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country |
      | Account_A | www.google.com | Apparel  | Analyst      | T1            | 555-555-0000 | 555-555-0001    | bob@bob.com | 555-555-0002 | tags | twitter | description | siccode  | tic           | 5000000        | 2         | Gates     | 0      | City 1               | Street address here    | 220051                     | WA                    | USA                     |
    Given I open about view and login
    When I choose Accounts in modules menu
    Then I should see *Account_A in #AccountsList.ListView
    Then I verify fields for *Account_A in #AccountsList.ListView
      | fieldName               | value                     |
      | name                    | <name>                    |
      | billing_address_city    | <billing_address_city>    |
      | billing_address_country | <billing_address_country> |
      | phone_office            | <phone_office>            |
      | email                   | <email>                   |

    # delete in list view
    When I click ToggleSidePanel button on #Account_ARecord header
    When I click on Delete button for *Account_A in #AccountsList.ListView
    When I Cancel confirmation alert
    Then I should see *Account_A in #AccountsList.ListView
    When I click on Delete button for *Account_A in #AccountsList.ListView
    When I Confirm confirmation alert
    When I close alert
    Then I should not see *Account_A in #AccountsList.ListView

    Examples:
      | name      | phone_office | email       | billing_address_city | billing_address_country |
      | Account_A | 555-555-0000 | bob@bob.com | City 1               | USA                     |

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
      | Account_A | www.google.com | Apparel  | Analyst      | T1            | 555-555-0000 | 555-555-0001    | bob@bob.com | 555-555-0002 | tags | twitter | description | siccode  | tic           | 5000000        | 2         | Gates     | 0      | City 1               | Street address here    | 220051                     | WA                    | USA                     |

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
      | name   | website    | industry   | account_type   | phone_office   | phone_alternate   | email   | phone_fax   | twitter   | description   | sic_code   | ticker_symbol   | annual_revenue   | employees   | ownership   | rating   | billing_address_city   | billing_address_street   | billing_address_postalcode   | billing_address_state   | billing_address_country   |
      | <name> | <website2> | <industry> | <account_type> | <phone_office> | <phone_alternate> | <email> | <phone_fax> | <twitter> | <description> | <sic_code> | <ticker_symbol> | <annual_revenue> | <employees> | <ownership> | <rating> | <billing_address_city> | <billing_address_street> | <billing_address_postalcode> | <billing_address_state> | <billing_address_country> |

    When I click on Cancel button in #Account_APreview.PreviewHeaderView

    # verify preview cancel
    Then I verify fields on #Account_APreview.PreviewView
      | fieldName                  | value                        |
      | name                       | <name>                       |
      | website                    | <http://><website1>          |
      | industry                   | <industry>                   |
      | account_type               | <account_type>               |
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
      | name   | website    | industry   | account_type   | phone_office   | phone_alternate   | email   | phone_fax   | twitter   | description   | sic_code   | ticker_symbol   | annual_revenue   | employees   | ownership   | rating   | billing_address_city   | billing_address_street   | billing_address_postalcode   | billing_address_state   | billing_address_country   |
      | <name> | <website2> | <industry> | <account_type> | <phone_office> | <phone_alternate> | <email> | <phone_fax> | <twitter> | <description> | <sic_code> | <ticker_symbol> | <annual_revenue> | <employees> | <ownership> | <rating> | <billing_address_city> | <billing_address_street> | <billing_address_postalcode> | <billing_address_state> | <billing_address_country> |

    When I click on Save button in #Account_APreview.PreviewHeaderView
    When I close alert

    # verify preview save
    Then I verify fields on #Account_APreview.PreviewView
      | fieldName                  | value                        |
      | name                       | <name>                       |
      | website                    | <http://><website2>          |
      | industry                   | <industry>                   |
      | account_type               | <account_type>               |
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
      | name      | http:// | website1       | website2      | industry | account_type | phone_office | phone_alternate | email       | phone_fax    | twitter | description | sic_code | ticker_symbol | annual_revenue | employees | ownership | rating | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country |
      | Account_A | http:// | www.google.com | www.yahoo.com | Apparel  | Analyst      | 555-555-0000 | 555-555-0001    | bob@bob.com | 555-555-0002 | twitter | description | siccode  | tic           | 5000000        | 2         | Gates     | 0      | City 1               | Street address here    | 220051                     | WA                    | USA                     |

  @share
  Scenario Outline: Accounts > List View > Record View > Share
    Given Accounts records exist:
      | *name     | website        | industry | account_type | service_level | phone_office | phone_alternate | email       | phone_fax    | tag  | twitter | description | sic_code | ticker_symbol | annual_revenue | employees | ownership | rating | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country |
      | Account_A | www.google.com | Apparel  | Analyst      | T1            | 555-555-0000 | 555-555-0001    | bob@bob.com | 555-555-0002 | tags | twitter | description | siccode  | tic           | 5000000        | 2         | Gates     | 0      | City 1               | Street address here    | 220051                     | WA                    | USA                     |
    Given I open about view and login
    When I choose Accounts in modules menu
    Then I should see *Account_A in #AccountsList.ListView
    When I select *Account_A in #AccountsList.ListView
    Then I should see #Account_ARecord view

   # Share Accounts > Cancel
    When I open actions menu in #Account_ARecord
    When I choose Share from actions menu in #Account_ARecord
    When I close alert
    When I click Cancel button on #EmailsDrawer header
    Then I should see #Account_ARecord view

   # Share Accounts > Save as Draft
    Given Accounts records exist:
      | *  | name     | email                       |
      | A1 | SugarCRM | admin@example.org (primary) |
    And Contacts records exist:
      | *  | first_name | last_name | email                      |
      | C1 | Will       | Westin    | will@example.org (primary) |
    And Leads records exist:
      | *  | first_name | last_name | email                     |
      | L1 | Max        | Jensen    | max@example.org (primary) |

    When I open actions menu in #Account_ARecord
    When I choose Share from actions menu in #Account_ARecord
   # Close warning about SMTP server not being configured
    When I close alert

   # Populate various email fields
    When I add the following recipients to the email in #EmailsRecord.RecordView
      | fieldName | value |
      | To        | *A1   |
      | Cc        | *C1   |
      | Bcc       | *L1   |
    When I click show more button on #EmailsDrawer view
    When I provide input for #EmailsRecord.RecordView view
      | *  | name            |
      | R1 | This is a test. |

   # Save email as draft
    When I click Save button on #EmailsRecord header
    When I close alert

   # Navigate to email record view
    When I choose Emails in modules menu
    When I select *R1 in #EmailsList.ListView
    When I close alert

   # Verify data in various email fields
    Then I verify fields on #R1Record.RecordView
      | fieldName  | value                                      |
      | name       | This is a test.                            |
      | recipients | SugarCRM; Cc: Will Westin; Bcc: Max Jensen |

    Examples:
      | name      | http:// | website1       | industry | account_type | service_level | phone_office | phone_alternate | email       | phone_fax    | twitter | description | sic_code | ticker_symbol | annual_revenue | employees | ownership | rating | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country |
      | Account_A | http:// | www.google.com | Apparel  | Analyst      | Tier 1        | 555-555-0000 | 555-555-0001    | bob@bob.com | 555-555-0002 | twitter | description | siccode  | tic           | 5000000        | 2         | Gates     | 0      | City 1               | Street address here    | 220051                     | WA                    | USA                     |

  @find-duplicates @ci-excluded
  Scenario: Accounts > Record View > Find Duplicates
    Given Accounts records exist:
      | *   | name      | phone_office | billing_address_city | billing_address_street | billing_address_country |
      | A_1 | Account_A | 555-555-0001 | City 1               | Street address here    | USA                     |
      | A_2 | Account_A | 555-555-0001 | City 1               | Street address here    | USA                     |
      | A_3 | Account_A | 555-555-0001 | City 1               | Street address here    | USA                     |

    # Find Duplicates Cancel/Save
    Given I open about view and login
    When I choose Accounts in modules menu
    Then I should see *A_1 in #AccountsList.ListView
    When I select *A_1 in #AccountsList.ListView
    Then I should see #A_1Record view
    When I open actions menu in #A_1Record
    When I choose FindDuplicates from actions menu in #A_1Record
    When I click Cancel button on #FindDuplicatesDrawer header
    Then I should see #A_1Record view

    When I open actions menu in #A_1Record
    When I choose FindDuplicates from actions menu in #A_1Record
    When I toggle checkbox for *A_3 in #FindDuplicatesDrawer.ListView
    When I toggle checkbox for *A_2 in #FindDuplicatesDrawer.ListView
    When I merge duplicates on #FindDuplicatesDrawer

    # Verification of accounts merge
    When I choose Accounts in modules menu
    Then I should see *A_1 in #AccountsList.ListView
    Then I should not see *A_3 in #AccountsList.ListView
    Then I should not see *A_2 in #AccountsList.ListView

  @copy
  Scenario Outline: Accounts > Record View > Copy > Cancel/Save
    Given Accounts records exist:
      | *name     | website        | industry | account_type | service_level | phone_office | phone_alternate | email                 | phone_fax    | tag  | twitter | description | sic_code | ticker_symbol | annual_revenue | employees | ownership | rating | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country |
      | Account_A | www.google.com | Apparel  | Analyst      | T1            | 555-555-0000 | 555-555-0001    | bob@bob.com (primary) | 555-555-0002 | tags | twitter | description | siccode  | tic           | 5000000        | 2         | Gates     | 0      | City 1               | Street address here    | 220051                     | WA                    | USA                     |
    Given I open about view and login
    When I choose Accounts in modules menu
    Then I should see *Account_A in #AccountsList.ListView
    When I select *Account_A in #AccountsList.ListView
    Then I should see #Account_ARecord view
    Then I click show more button on #Account_ARecord view

   # Copy Accounts > Cancel
    When I open actions menu in #Account_ARecord
    When I choose Copy from actions menu in #Account_ARecord
    When I provide input for #AccountsDrawer.HeaderView view
      | name    |
      | <name2> |
    When I provide input for #AccountsDrawer.RecordView view
      | website    | industry    | account_type    | phone_office    | phone_alternate    | email    | phone_fax    | twitter    | description    | sic_code    | ticker_symbol    | annual_revenue    | employees    | ownership    | rating    | billing_address_city    | billing_address_street    | billing_address_postalcode    | billing_address_state    | billing_address_country    |
      | <website2> | <industry2> | <account_type2> | <phone_office2> | <phone_alternate2> | <email2> | <phone_fax2> | <twitter2> | <description2> | <sic_code2> | <ticker_symbol2> | <annual_revenue2> | <employees2> | <ownership2> | <rating2> | <billing_address_city2> | <billing_address_street2> | <billing_address_postalcode2> | <billing_address_state2> | <billing_address_country2> |
    When I click Cancel button on #AccountsDrawer header
    Then I verify fields on #Account_ARecord
      | fieldName                  | value                        |
      | name                       | <name>                       |
      | website                    | <http://><website1>          |
      | industry                   | <industry>                   |
      | account_type               | <account_type>               |
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

    # Copy Accounts > Save
    When I open actions menu in #Account_ARecord
    When I choose Copy from actions menu in #Account_ARecord
    When I provide input for #AccountsDrawer.HeaderView view
      | name    |
      | <name2> |
    When I provide input for #AccountsDrawer.RecordView view
      | website    | industry    | account_type    | phone_office    | phone_alternate    | email    | phone_fax    | twitter    | description    | sic_code    | ticker_symbol    | annual_revenue    | employees    | ownership    | rating    | billing_address_city    | billing_address_street    | billing_address_postalcode    | billing_address_state    | billing_address_country    |
      | <website2> | <industry2> | <account_type2> | <phone_office2> | <phone_alternate2> | <email2> | <phone_fax2> | <twitter2> | <description2> | <sic_code2> | <ticker_symbol2> | <annual_revenue2> | <employees2> | <ownership2> | <rating2> | <billing_address_city2> | <billing_address_street2> | <billing_address_postalcode2> | <billing_address_state2> | <billing_address_country2> |
    When I click Save button on #AccountsDrawer header
    When I close alert
    Then I verify fields on #Account_ARecord
      | fieldName                  | value                         |
      | name                       | <name2>                       |
      | website                    | <http://><website2>           |
      | industry                   | <industry2>                   |
      | account_type               | <account_type2>               |
      | phone_office               | <phone_office2>               |
      | phone_alternate            | <phone_alternate2>            |
      | email                      | <email2>                      |
      | phone_fax                  | <phone_fax2>                  |
      | twitter                    | <twitter2>                    |
      | description                | <description2>                |
      | sic_code                   | <sic_code2>                   |
      | ticker_symbol              | <ticker_symbol2>              |
      | annual_revenue             | <annual_revenue2>             |
      | employees                  | <employees2>                  |
      | ownership                  | <ownership2>                  |
      | rating                     | <rating2>                     |
      | billing_address_city       | <billing_address_city2>       |
      | billing_address_street     | <billing_address_street2>     |
      | billing_address_postalcode | <billing_address_postalcode2> |
      | billing_address_state      | <billing_address_state2>      |
      | billing_address_country    | <billing_address_country2>    |

    Examples:
      | name      | name2     | http:// | website1       | website2      | industry | industry2 | account_type | account_type2 | service_level | service_level2 | phone_office | phone_office2 | phone_alternate | phone_alternate2 | email       | email2            | email_type | phone_fax    | phone_fax2   | twitter | twitter2 | description | description2 | sic_code | sic_code2 | ticker_symbol | ticker_symbol2 | annual_revenue | annual_revenue2 | employees | employees2 | ownership | ownership2 | rating | rating2 | billing_address_city | billing_address_city2 | billing_address_street | billing_address_street2 | billing_address_postalcode | billing_address_postalcode2 | billing_address_state | billing_address_state2 | billing_address_country | billing_address_country2 |
      | Account_A | Account_B | http:// | www.google.com | www.yahoo.com | Apparel  | Banking   | Analyst      | Customer      | Tier 1        | Tier 2         | 555-555-0000 | 666-666-0000  | 555-555-0001    | 666-666-0001     | bob@bob.com | bobber@bobber.com | (primary)  | 555-555-0002 | 666-666-0002 | twitter | twitter2 | description | description2 | siccode  | siccode2  | tic           | tic2           | 5000000        | 250000          | 2         | 10         | Gates     | Musk       | 0      | 100     | City 1               | City 2                | Street address here    | Street address there    | 220051                     | 95014                       | WA                    | CA                     | USA                     | UK                       |

  @create_new_account
  Scenario Outline: Account > Create > Cancel/Save
    Given I open about view and login
    When I choose Accounts in modules menu

    # Create Account > Cancel
    When I click Create button on #AccountsList header
    When I click show more button on #AccountsDrawer view
    When I provide input for #AccountsDrawer.HeaderView view
      | name   |
      | <name> |
    When I provide input for #AccountsDrawer.RecordView view
      | website   | industry   | account_type   | phone_office   | phone_alternate   | email   | phone_fax   | twitter   | description   | sic_code   | ticker_symbol   | annual_revenue   | employees   | ownership   | rating   | billing_address_city   | billing_address_street   | billing_address_postalcode   | billing_address_state   | billing_address_country   |
      | <website> | <industry> | <account_type> | <phone_office> | <phone_alternate> | <email> | <phone_fax> | <twitter> | <description> | <sic_code> | <ticker_symbol> | <annual_revenue> | <employees> | <ownership> | <rating> | <billing_address_city> | <billing_address_street> | <billing_address_postalcode> | <billing_address_state> | <billing_address_country> |
    When I click Cancel button on #AccountsDrawer header
    Then I should see #AccountsList.ListView view

    # Create Accounts > Save
    When I click Create button on #AccountsList header
    When I click show more button on #AccountsDrawer view
    When I provide input for #AccountsDrawer.HeaderView view
      | *   | name   |
      | A_1 | <name> |
    When I provide input for #AccountsDrawer.RecordView view
      | *   | website   | industry   | account_type   | phone_office   | phone_alternate   | email   | phone_fax   | twitter   | description   | sic_code   | ticker_symbol   | annual_revenue   | employees   | ownership   | rating   | billing_address_city   | billing_address_street   | billing_address_postalcode   | billing_address_state   | billing_address_country   |
      | A_1 | <website> | <industry> | <account_type> | <phone_office> | <phone_alternate> | <email> | <phone_fax> | <twitter> | <description> | <sic_code> | <ticker_symbol> | <annual_revenue> | <employees> | <ownership> | <rating> | <billing_address_city> | <billing_address_street> | <billing_address_postalcode> | <billing_address_state> | <billing_address_country> |
    When I click Save button on #AccountsDrawer header
    When I close alert
    Then I should see *A_1 in #AccountsList.ListView
    When I select *A_1 in #AccountsList.ListView
    Then I should see #A_1Record view
    Then I click show more button on #A_1Record view

    #Verify Account creation
    Then I verify fields on #A_1Record
      | fieldName                  | value                        |
      | name                       | <name>                       |
      | website                    | <http://><website>           |
      | industry                   | <industry>                   |
      | account_type               | <account_type>               |
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
      | name      | http:// | website        | industry | account_type | phone_office | phone_alternate | email       | phone_fax    | twitter | description | sic_code | ticker_symbol | annual_revenue | employees | ownership | rating | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country |
      | Account_A | http:// | www.google.com | Apparel  | Analyst      | 555-555-0000 | 555-555-0001    | bob@bob.com | 555-555-0002 | twitter | description | siccode  | tic           | 5000000        | 2         | Gates     | 0      | City 1               | Street address here    | 220051                     | WA                    | USA                     |

  @member_of
  Scenario: Account > Edit > Member Of
    # This is to test out the member of functionality for the accounts records
    Given Accounts records exist:
      | *name     | website        | industry | account_type | service_level | phone_office | phone_alternate | email                        | phone_fax    | tag   | twitter  | description  | sic_code | ticker_symbol | annual_revenue | employees | ownership | rating | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country |
      | Account_A | www.google.com | Apparel  | Analyst      | T1            | 555-555-0000 | 555-555-0001    | bob@bob.com        (primary) | 555-555-0002 | tags  | twitter  | description  | siccode  | tic           | 5000000        | 2         | Gates     | 0      | City 1               | Street address here    | 220051                     | WA                    | USA                     |
      | Account_B | www.yahoo.com  | Banking  | Customer     | T2            | 666-666-0000 | 666-666-0001    | bobbber@bobber.com (primary) | 666-666-0002 | tags2 | twitter2 | description2 | siccode2 | tic2          | 250            | 2         | Musk      | 10     | City 2               | Street address there   | 95112                      | CA                    | UK                      |

    Given I open about view and login
    When I choose Accounts in modules menu
    Then I should see *Account_A in #AccountsList.ListView
    When I select *Account_A in #AccountsList.ListView
    Then I should see #Account_ARecord view
    When I provide input for #Account_ARecord
      | parent_name |
      | Account_B   |
    When I choose Accounts in modules menu
    Then I should see *Account_B in #AccountsList.ListView
    When I select *Account_B in #AccountsList.ListView
    Then I should see #Account_BRecord view
    When I open the members subpanel on #Account_BRecord view
    Then I verify number of records in #Account_BRecord.SubpanelsLayout.subpanels.members is 1
    Then I should see *Account_A in #Account_BRecord.SubpanelsLayout.subpanels.members
    Then I verify fields for *Account_A in #Account_BRecord.SubpanelsLayout.subpanels.members
      | fieldName | value     |
      | name      | Account_A |
