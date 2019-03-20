# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@crud_modules_prospects @job1
Feature: Prospects module verification

  Background:
    Given I am logged in

  @list
  Scenario: Prospects > List View > Preview
    Given Prospects records exist:
      | *    | first_name | last_name | account_name   | title             | phone_mobile   | phone_work     | primary_address_street | primary_address_city | primary_address_state | primary_address_postalcode | email            |
      | Pr_1 | ProspectF  | ProspectL | John's Account | Software Engineer | (746) 079-5067 | (408) 536-6312 | 10050 N Wolfe Rd       | Cupertino            | California            | 95014                      | John@example.org |

    Then Prospects *Pr_1 should have the following values in the preview:
      | fieldName              | value               |
      | name                   | ProspectF ProspectL |
      | account_name           | John's Account      |
      | title                  | Software Engineer   |
      | email                  | John@example.org    |
      | phone_mobile           | (746) 079-5067      |
      | phone_work             | (408) 536-6312      |
      | primary_address_street | 10050 N Wolfe Rd    |


  @list-search
  Scenario Outline: Prospects > List View > Filter > Search main input
    Given 3 Prospects records exist:
      | *            | first_name        | last_name         | account_name              | title                      | email                    |
      | Pr_{{index}} | Prospect{{index}} | Prospect{{index}} | Prospect{{index}} Account | Software Engineer{{index}} | Pr_{{index}}@example.net |
    # Search for specific record
    When I choose Prospects in modules menu
    And I search for "Prospect<searchIndex>" in #ProspectsList.FilterView view
    # Verification if filtering is successful
    Then I should see [*Pr_<searchIndex>] on Prospects list view
    And I should not see [*Pr_1, *Pr_3] on Prospects list view
    Examples:
      | searchIndex |
      | 2           |

  @list-edit
  Scenario Outline: Prospects > List View > Inline Edit > Cancel/Save
    Given Prospects records exist:
      | *    | first_name | last_name | phone_work     | title               | email            |
      | Pr_1 | Prospect1  | Prospect1 | (408) 536-0001 | Software Engineer 1 | Pr_1@example.net |

    # Edit (or cancel editing of) record in the list view
    When I <action> *Pr_1 record in Prospects list view with the following values:
      | fieldName  | value                           |
      | first_name | Prospect<changeIndex>           |
      | last_name  | Prospect<changeIndex>           |
      | title      | Software Engineer <changeIndex> |
      | phone_work | (408) 536-000<changeIndex>      |
      | email      | Pr_<changeIndex>@example.net    |

    # Verify if edit (or cancel) is successful
    Then Prospects *Pr_1 should have the following values in the list view:
      | fieldName  | value                                           |
      | name       | Prospect<expectedIndex> Prospect<expectedIndex> |
      | title      | Software Engineer <expectedIndex>               |
      | email      | Pr_<expectedIndex>@example.net                  |
      | phone_work | (408) 536-000<expectedIndex>                    |

    Examples:
      | action            | changeIndex | expectedIndex |
      | edit              | 2           | 2             |
      | cancel editing of | 2           | 1             |


  @list-delete
  Scenario Outline: Prospects > List View > Delete > OK/Cancel
    Given Prospects records exist:
      | *    | first_name | last_name | phone_work     | title             | email            |
      | Pr_1 | Prospect1  | Prospect1 | (408) 536-0000 | Software Engineer | Pr_1@example.net |
    # Delete (or Cancel deletion of) record from list view
    When I <action> *Pr_1 record in Prospects list view
    # Verify that record is (is not) deleted
    Then I <expected> see [*Pr_1] on Prospects list view
    Examples:
      | action             | expected   |
      | delete             | should not |
      | cancel deletion of | should     |

  @delete
  Scenario Outline: Prospects > Record View > Delete
    Given Prospects records exist:
      | *    | first_name | last_name | phone_work     | title             | email            |
      | Pr_1 | Prospect1  | Prospect1 | (408) 536-0000 | Software Engineer | Pr_1@example.net |

    # Delete (or Cancel deletion of) record in the record view
    When I <action> *Pr_1 record in Prospects record view

    # Verify that record is (is not) deleted
    When I choose Prospects in modules menu
    Then I <expected> see [*Pr_1] on Prospects list view
    Examples:
      | action             | expected   |
      | delete             | should not |
      | cancel deletion of | should     |

  @copy
  Scenario Outline: Prospects > Record View > Copy > Save/Cancel
    Given Prospects records exist:
      | *    | first_name | last_name | title             | email                      |
      | Pr_1 | Prospect1  | Prospect1 | Software Engineer | pr_1@example.net (primary) |

    # Copy (or cancel copy of) record in the record view
    When I <action> *Pr_1 record in Prospects record view with the following header values:
      | *    | first_name            | last_name             |
      | Pr_2 | Prospect<changeIndex> | Prospect<changeIndex> |

    # Verify if copy is (is not) created
    Then Prospects *Pr_<expectedIndex> should have the following values:
      | fieldName | value                                           |
      | name      | Prospect<expectedIndex> Prospect<expectedIndex> |
      | title     | Software Engineer                               |
      | email     | pr_1@example.net                                |

    Examples:
      | action         | changeIndex | expectedIndex |
      | cancel copy of | 2           | 1             |
      | copy           | 2           | 2             |

  @create
  Scenario: Prospects > Create
    # Click Create Prospect in Mega menu
    When I choose Prospects in modules menu and select "Create Target" menu item
    When I click show more button on #ProspectsDrawer view
    # Populate Header data
    When I provide input for #ProspectsDrawer.HeaderView view
      | *    | salutation | first_name | last_name |
      | Pr_1 | Dr.        | Target1    | Target1   |
    # Populate record data
    When I provide input for #ProspectsDrawer.RecordView view
      | *    | phone_work     | title | email            | account_name | phone_mobile   | department  | do_not_call | tag       | primary_address_street | primary_address_city | primary_address_state | primary_address_postalcode | description | copy |
      | Pr_1 | (408) 233-3221 | CTO   | pr_1@example.net | Test Account | (408) 322-4321 | Engineering | True        | prospect1 | 4307 Emperor Blvd.     | Durham               | NC                    | 27703                      | New Target  | true |
    # Save
    When I click Save button on #ProspectsDrawer header
    When I close alert
    # Verify that record is created successfully
    Then Prospects *Pr_1 should have the following values:
      | fieldName                  | value               |
      | name                       | Dr. Target1 Target1 |
      | title                      | CTO                 |
      | email                      | pr_1@example.net    |
      | account_name               | Test Account        |
      | phone_mobile               | (408) 322-4321      |
      | department                 | Engineering         |
      | do_not_call                | true                |
      | primary_address_street     | 4307 Emperor Blvd.  |
      | primary_address_city       | Durham              |
      | primary_address_state      | NC                  |
      | primary_address_postalcode | 27703               |
      | description                | New Target          |
      | alt_address_street         | 4307 Emperor Blvd.  |
      | alt_address_city           | Durham              |
      | alt_address_state          | NC                  |
      | alt_address_postalcode     | 27703               |
      | description                | New Target          |


  @convert_target @pr
  Scenario: Prospects > Record View > Convert > Save
    Given Prospects records exist:
      | *    | first_name | last_name | title             | email                      | account_name     |
      | Pr_1 | Prospect1  | Prospect1 | Software Engineer | pr_1@example.net (primary) | Prospect Account |

    # Copy (or cancel copy of) record in the record view
    When I convert *Pr_1 record in Prospects record view with the following header values:
      | *   | first_name | last_name |
      | L_1 | Lead_F     | Lead_L    |

    # Verify that conversion is completed succ
    Then Leads *L_1 should have the following values:
      | fieldName    | value             |
      | name         | Lead_F Lead_L     |
      | title        | Software Engineer |
      | email        | pr_1@example.net  |
      | account_name | Prospect Account  |
