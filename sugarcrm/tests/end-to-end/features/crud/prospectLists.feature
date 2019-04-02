# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@crud_modules_prospect_lists @job2
Feature: Prospects module verification

  Background:
    Given I am logged in

  @list
  Scenario: Prospect Lists > List View > Preview
    Given ProspectLists records exist:
      | *name | description        | list_type |
      | PRL_1 | New Prospect Lists | seed      |

    Then ProspectLists *PRL_1 should have the following values in the preview:
      | fieldName   | value              |
      | name        | PRL_1              |
      | description | New Prospect Lists |
      | list_type   | Seed               |
      | entry_count | 0                  |


  @list-search
  Scenario Outline: Prospect Lists > List View > Filter > Search main input
    Given 3 ProspectLists records exist:
      | *name         | description             | list_type |
      | PRL_{{index}} | Prospect List {{index}} | seed      |

    # Search for specific record
    When I choose ProspectLists in modules menu
    And I search for "PRL_<searchIndex>" in #ProspectListsList.FilterView view
    # Verification if filtering is successful
    Then I should see [*PRL_<searchIndex>] on ProspectLists list view
    And I should not see [*PRL_1, *PRL_3] on ProspectLists list view
    Examples:
      | searchIndex |
      | 2           |

  @list-edit
  Scenario Outline: Prospect Lists > List View > Inline Edit > Cancel/Save
    Given ProspectLists records exist:
      | *name | description     | list_type     | domain_name  |
      | PRL_1 | Prospect List 1 | exempt_domain | sugarcrm.com |

    # Edit (or cancel editing of) record in the list view
    # Description field is not editable in the list view
    When I <action> *PRL_1 record in ProspectLists list view with the following values:
      | fieldName | value             |
      | name      | PRL_<changeIndex> |
      | list_type | <changeDomain>    |

    # Verify if edit (or cancel) is successful
    Then ProspectLists *PRL_1 should have the following values in the list view:
      | fieldName   | value               |
      | name        | PRL_<expectedIndex> |
      | description | Prospect List 1     |
      | list_type   | <expectedDomain>    |

    Examples:
      | action            | changeIndex | expectedIndex | changeDomain | expectedDomain               |
      | edit              | 2           | 2             | Seed         | Seed                         |
      | cancel editing of | 2           | 1             | Seed         | Suppression List - By Domain |


  @list-delete
  Scenario Outline: Prospect Lists > List View > Delete > OK/Cancel
    Given ProspectLists records exist:
      | *name | description     | list_type     | domain_name  |
      | PRL_1 | Prospect List 1 | exempt_domain | sugarcrm.com |

    # Delete (or Cancel deletion of) record from list view
    When I <action> *PRL_1 record in ProspectLists list view

    # Verify that record is (is not) deleted
    Then I <expected> see [*PRL_1] on ProspectLists list view
    Examples:
      | action             | expected   |
      | delete             | should not |
      | cancel deletion of | should     |

  @delete
  Scenario Outline: ProspectLists > Record View > Delete
    Given ProspectLists records exist:
      | *name | description     | list_type     | domain_name  |
      | PRL_1 | Prospect List 1 | exempt_domain | sugarcrm.com |

    # Delete (or Cancel deletion of) record in the record view
    When I <action> *PRL_1 record in ProspectLists record view

    # Verify that record is (is not) deleted
    When I choose ProspectLists in modules menu
    Then I <expected> see [*PRL_1] on ProspectLists list view
    Examples:
      | action             | expected   |
      | delete             | should not |
      | cancel deletion of | should     |

  @copy
  Scenario Outline: Prospect Lists > Record View > Copy > Save/Cancel
    Given ProspectLists records exist:
      | *name | description     | list_type     | domain_name  |
      | PRL_1 | Prospect List 1 | exempt_domain | sugarcrm.com |

    # Copy (or cancel copy of) record in the record view
    When I <action> *PRL_1 record in ProspectLists record view with the following header values:
      | *name             |
      | PRL_<changeIndex> |

    # Verify if copy is (is not) created
    Then ProspectLists *PRL_<expectedIndex> should have the following values:
      | fieldName   | value                        |
      | name        | PRL_<expectedIndex>          |
      | list_type   | Suppression List - By Domain |
      | domain_name | sugarcrm.com                 |

    Examples:
      | action         | changeIndex | expectedIndex |
      | cancel copy of | 2           | 1             |
      | copy           | 2           | 2             |

  @create
  Scenario: Prospect Lists > Create
    # Click Create Prospect in Mega menu
    When I choose ProspectLists in modules menu and select "Create Target List" menu item
    When I click show more button on #ProspectListsDrawer view
    # Populate Header data
    When I provide input for #ProspectListsDrawer.HeaderView view
      | *     | name            |
      | PRL_1 | 2019 March List |
    # Populate record data
    When I provide input for #ProspectListsDrawer.RecordView view
      | *     | description              | list_type                           |
      | PRL_1 | March 2019 Prospect List | Suppression List - By Email Address |
    # Save
    When I click Save button on #ProspectListsDrawer header
    When I close alert
    # Verify that record is created successfully
    Then ProspectLists *PRL_1 should have the following values:
      | fieldName   | value                               |
      | name        | 2019 March List                     |
      | description | March 2019 Prospect List            |
      | list_type   | Suppression List - By Email Address |
      | entry_count | 0                                   |


  @add_to_the_list
  Scenario: Prospect Lists > Subpanel >List existing records
    Given ProspectLists records exist:
      | *name | description     | list_type     | domain_name  |
      | PRL_1 | Prospect List 1 | exempt_domain | sugarcrm.com |

    And Prospects records exist:
      | *    | first_name | last_name | phone_work     | title               | email                      |
      | Pr_1 | Prospect1  | Prospect1 | (408) 536-0001 | Software Engineer 1 | Pr_1@example.net (primary) |

    And Contacts records exist:
      | *    | first_name | last_name | email                      | title               |
      | Co_1 | Contact1   | Contact1  | Co_1@example.net (primary) | Automation Engineer |

    And Leads records exist:
      | *    | first_name | last_name | account_name  | title             | phone_mobile   | phone_work     | email                      |
      | Le_1 | Lead1      | Lead1     | John's Acount | Software Engineer | (746) 079-5067 | (408) 536-6312 | Le_1@example.new (primary) |

    And Accounts records exist:
      | *name | billing_address_city | billing_address_street | billing_address_postalcode | billing_address_state | billing_address_country | email            |
      | Ac_1  | City 1               | Street address here    | 220051                     | WA                    | USA                     | Ac_1@example.org |

    And Users records exist:
      | *    | status | user_name | user_hash | last_name | first_name | email              |
      | Us_1 | Active | user_1    | LOGIN     | uLast_1   | uFirst_1   | user_1@example.org |

    When I open ProspectLists *PRL_1 record view
    And I link existing record *Pr_1 to prospects subpanel on #PRL_1Record view
    And I link existing record *Co_1 to contacts subpanel on #PRL_1Record view
    And I link existing record *Le_1 to leads subpanel on #PRL_1Record view
    And I link existing record *Ac_1 to accounts subpanel on #PRL_1Record view
    And I link existing record *Us_1 to users subpanel on #PRL_1Record view

    Then ProspectLists *PRL_1 should have the following values in the preview:
      | fieldName   | value |
      | entry_count | 5     |


  @add_targets_found_by_report
  Scenario: Prospect Lists > Subpanel > Add Prospects from report
    Given ProspectLists records exist:
      | *name | description     | list_type     | domain_name  |
      | PRL_1 | Prospect List 1 | exempt_domain | sugarcrm.com |

    And 3 Prospects records exist:
      | *            | first_name        | last_name         | phone_work     | title              | email                              |
      | Pr_{{index}} | Prospect{{index}} | Prospect{{index}} | (408) 536-0001 | Engineer {{index}} | Pr_{{index}}@example.net (primary) |

    When I open ProspectLists *PRL_1 record view
    And I link prospects returned by 'Count of Targets by Country' report to #PRL_1Record target list

    # Verify number of records in Prospects subpanel
    Then I verify number of records in #PRL_1Record.SubpanelsLayout.subpanels.prospects is 3


  @add_contacts_found_by_report
  Scenario Outline: Prospect Lists > Subpanel > Add Contacts from report
    Given ProspectLists records exist:
      | *name | description     | list_type     | domain_name  |
      | PRL_1 | Prospect List 1 | exempt_domain | sugarcrm.com |

    And 3 Contacts records exist:
      | *            | first_name       | last_name        | phone_work     | title               | email                              |
      | Co_{{index}} | Contact{{index}} | Contact{{index}} | (408) 536-0002 | Architect {{index}} | Co_{{index}}@example.net (primary) |

    When I open ProspectLists *PRL_1 record view
    And I link contacts returned by '<report1>' report to #PRL_1Record target list
    Then I verify and close alert
      | type    | message                     |
      | warning | Warning No records to link. |
    Then I verify number of records in #PRL_1Record.SubpanelsLayout.subpanels.contacts is 0

    When I link contacts returned by '<report2>' report to #PRL_1Record target list
    # Verify number of records in Contacts subpanel
    Then I verify number of records in #PRL_1Record.SubpanelsLayout.subpanels.contacts is 3
    Then I should see *Co_1 in #PRL_1Record.SubpanelsLayout.subpanels.contacts
    Then I should see *Co_2 in #PRL_1Record.SubpanelsLayout.subpanels.contacts
    Then I should see *Co_3 in #PRL_1Record.SubpanelsLayout.subpanels.contacts

    Examples:
      | report1                           | report2                      |
      | Contacts Created by User by Month | Count of Contacts by Country |


  @add_leads_found_by_report @pr
  Scenario Outline: Prospect Lists > Subpanel > Add Leads from report
    Given ProspectLists records exist:
      | *name | description     | list_type     | domain_name  |
      | PRL_1 | Prospect List 1 | exempt_domain | sugarcrm.com |

    And 3 Leads records exist:
      | *            | first_name    | last_name     | phone_work     | title               | email                              | lead_source | status |
      | Le_{{index}} | Lead{{index}} | Lead{{index}} | (408) 536-0002 | Architect {{index}} | Le_{{index}}@example.net (primary) | Cold Call   | New    |
    And Leads records exist:
      | *    | first_name | last_name | phone_work     | title       | email                      | lead_source | status |
      | Le_4 | Lead4      | Lead4     | (408) 536-0002 | Architect 4 | Le_4@example.net (primary) | Employee    | Dead   |

    When I open ProspectLists *PRL_1 record view
    And I link leads returned by '<report1>' report to #PRL_1Record target list
    When I verify and close alert
      | type    | message                     |
      | warning | Warning No records to link. |
    Then I verify number of records in #PRL_1Record.SubpanelsLayout.subpanels.leads is 0

    When I link leads returned by '<report2>' report to #PRL_1Record target list
    Then I verify and close alert
      | type    | message                             |
      | Success | Success 4 records have been linked. |

    # Verify number of records in Leads subpanel
    Then I should see *Le_1 in #PRL_1Record.SubpanelsLayout.subpanels.leads
    And I should see *Le_2 in #PRL_1Record.SubpanelsLayout.subpanels.leads
    And I should see *Le_3 in #PRL_1Record.SubpanelsLayout.subpanels.leads
    And I should see *Le_4 in #PRL_1Record.SubpanelsLayout.subpanels.leads

    Examples:
      | report1                 | report2              |
      | Leads Converted by User | Leads By Lead Source |


  @add_accounts_found_by_report
  Scenario Outline: Prospect Lists > Subpanel > Add Accounts from report
    Given ProspectLists records exist:
      | *name | description     | list_type     | domain_name  |
      | PRL_1 | Prospect List 1 | exempt_domain | sugarcrm.com |

    And 2 Accounts records exist:
      | *            | name             | type    | industry |
      | Ac_{{index}} | Account{{index}} | Analyst | Banking  |

    And Accounts records exist:
      | *    | name     | type    | industry      |
      | Ac_3 | Account3 | Banking | Biotechnology |

    When I open ProspectLists *PRL_1 record view
    And I link accounts returned by '<report1>' report to #PRL_1Record target list
    When I verify and close alert
      | type    | message                     |
      | warning | Warning No records to link. |
    Then I verify number of records in #PRL_1Record.SubpanelsLayout.subpanels.accounts is 0

    When I link accounts returned by '<report2>' report to #PRL_1Record target list
    # Verify number of records in Accounts subpanel
    Then I verify number of records in #PRL_1Record.SubpanelsLayout.subpanels.accounts is 3
    And I should see *Ac_1 in #PRL_1Record.SubpanelsLayout.subpanels.accounts
    And I should see *Ac_2 in #PRL_1Record.SubpanelsLayout.subpanels.accounts
    And I should see *Ac_3 in #PRL_1Record.SubpanelsLayout.subpanels.accounts

    Examples:
      | report1                  | report2                      |
      | My New Customer Accounts | Accounts By Type By Industry |


  @add_users_found_by_report
  Scenario Outline: Prospect Lists > Subpanel > Add Users from report
    Given ProspectLists records exist:
      | *name | description     | list_type     | domain_name  |
      | PRL_1 | Prospect List 1 | exempt_domain | sugarcrm.com |

    And Users records exist:
      | *    | status | user_name | user_hash | last_name | first_name | email              |
      | Us_1 | Active | user_1    | LOGIN     | uLast_1   | uFirst_1   | user_1@example.org |

    When I open ProspectLists *PRL_1 record view
    And I link users returned by '<report1>' report to #PRL_1Record target list

    # Verify number of records in Users subpanel
    Then I verify number of records in #PRL_1Record.SubpanelsLayout.subpanels.users is 2
    And I should see *Us_1 in #PRL_1Record.SubpanelsLayout.subpanels.users

    Examples:
      | report1            |
      | Licensed User List |
