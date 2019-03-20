# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@modules @target_list @job4
Feature: Adding records to Target lists:
  As a Sugar user, I need to be able to add records to target list directly from list view of the the records' module

  Background:
    Given I am logged in


  Scenario Outline: Leads > List View > Add records to NEW Target List >  OK / Cancel
    Given 3 Leads records exist:
      | *           | first_name    | last_name     | account_name          | title             | email                                     |
      | L_{{index}} | lead{{index}} | lead{{index}} | Lead{{index}} Account | Software Engineer | lead{{index}}.sugar@example.org (primary) |

    # Add (or cancel adding) records to newly created target list
    When I <action> Leads [*L_1, *L_2, *L_3] to new target list with the following values:
      | *     | name            |
      | PRL_1 | New Target List |

    # Verify that new target list is created and records are successfully added
    Then ProspectLists *PRL_1 should have the following values:
      | fieldName   | value           |
      | name        | New Target List |
      | entry_count | <expected>      |

    # Verify number of records in Prospects subpanel
    When I open the leads subpanel on #PRL_1Record view
    Then I verify number of records in #PRL_1Record.SubpanelsLayout.subpanels.leads is <expected>

    Examples:
      | action        | expected |
      | add           | 3        |
      | cancel adding | 0        |


  Scenario Outline: Leads > List View > Add records to Existing Target List > OK / Cancel
    Given ProspectLists records exist:
      | *name | description     | list_type     | domain_name  |
      | PRL_1 | Prospect List 1 | exempt_domain | sugarcrm.com |

    And 3 Leads records exist:
      | *           | first_name    | last_name     | account_name          | title             | email                                     |
      | L_{{index}} | lead{{index}} | lead{{index}} | Lead{{index}} Account | Software Engineer | lead{{index}}.sugar@example.org (primary) |

    # Add (or cancel adding) records to newly created target list
    When I <action> Leads [*L_1, *L_2, *L_3] to *PRL_1 target list

    # Verify that records are added successfully to the existing target list
    Then ProspectLists *PRL_1 should have the following values:
      | fieldName   | value      |
      | name        | PRL_1      |
      | entry_count | <expected> |

    When I open the leads subpanel on #PRL_1Record view
    Then I verify number of records in #PRL_1Record.SubpanelsLayout.subpanels.leads is <expected>

    Examples:
      | action        | expected |
      | add           | 3        |
      | cancel adding | 0        |


  @pr
  Scenario: Various Modules > List View > Add records to Existing Target List
    Given ProspectLists records exist:
      | *     | name     | description     | list_type     | domain_name  |
      | PRL_1 | New List | Prospect List 1 | exempt_domain | sugarcrm.com |

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

    # Add records to target list
    When I add Prospects [*Pr_1] to *PRL_1 target list
    And  I add Contacts [*Co_1] to *PRL_1 target list
    And  I add Leads [*Le_1] to *PRL_1 target list
    And  I add Accounts [*Ac_1] to *PRL_1 target list

    # Verify that records are added successfully to the existing target list
    Then ProspectLists *PRL_1 should have the following values:
      | fieldName   | value    |
      | name        | New List |
      | entry_count | 4        |

    When I open the prospects subpanel on #PRL_1Record view
    Then I verify number of records in #PRL_1Record.SubpanelsLayout.subpanels.prospects is 1

    When I open the contacts subpanel on #PRL_1Record view
    Then I verify number of records in #PRL_1Record.SubpanelsLayout.subpanels.contacts is 1

    When I open the leads subpanel on #PRL_1Record view
    Then I verify number of records in #PRL_1Record.SubpanelsLayout.subpanels.leads is 1

    When I open the accounts subpanel on #PRL_1Record view
    Then I verify number of records in #PRL_1Record.SubpanelsLayout.subpanels.accounts is 1
