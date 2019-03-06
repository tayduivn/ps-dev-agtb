# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@modules @leads_merge @job6
Feature: Merge verification:
  As a Sugar user, I need to be able to merge selected records in SIDECAR list view

  Background:
    Given I am logged in

  Scenario: Leads > List View > Merge records with NO prior changes > Save
    Given Leads records exist:
      | *   | first_name | last_name | account_name  | title              | email                             |
      | L_1 | lead1      | lead1     | Lead1 Account | Software Engineer  | lead1.sugar@example.org (primary) |
      | L_2 | lead2      | lead2     | Lead2 Account | Software Developer | lead2.sugar@example.org (primary) |
      | L_3 | lead3      | lead3     | Lead3 Account | Software Architect | lead3.sugar@example.org (primary) |

    # Perform records merge with no prior edit
    When I perform merge of Leads [*L_1, *L_2, *L_3] records

    # Verify that records are successfully merged
    Then I should not see [*L_2, *L_3] on Leads list view
    And Leads *L_1 should have the following values:
      | fieldName    | value                   |
      | name         | lead1 lead1             |
      | account_name | Lead1 Account           |
      | email        | lead1.sugar@example.org |
      | title        | Software Engineer       |


  @pr
  Scenario: Leads > List View > Merge > Make changes to some of the primary record's fields before merge > Save
    Given Leads records exist:
      | *   | first_name | last_name | account_name  | title              | email                             |
      | L_1 | lead1      | lead1     | Lead1 Account | Software Engineer  | lead1.sugar@example.org (primary) |
      | L_2 | lead2      | lead2     | Lead2 Account | Software Developer | lead2.sugar@example.org (primary) |
      | L_3 | lead3      | lead3     | Lead3 Account | Software Architect | lead3.sugar@example.org (primary) |

    # Perform records merge with changing some field values of primary record prior to the merge
    When I perform merge of Leads [*L_1, *L_2, *L_3] records with the following changes:
      | fieldName  | value                   |
      | first_name | Jessica                 |
      | last_name  | Cho                     |
      | title      | Director Sales          |
      | email      | jessica_cho@example.com |

    # Verify that records are successfully merged
    Then I should not see [*L_2, *L_3] on Leads list view
    And Leads *L_1 should have the following values:
      | fieldName    | value                   |
      | name         | Jessica Cho             |
      | account_name | Lead1 Account           |
      | email        | jessica_cho@example.com |
      | title        | Director Sales          |


  @pr
  Scenario: Leads > List View > Merge > Change some fields from secondary record to be primary fields > Save
    Given Leads records exist:
      | *   | first_name | last_name | account_name  | title              | email                             |
      | L_1 | lead1      | lead1     | Lead1 Account | Software Engineer  | lead1.sugar@example.org (primary) |
      | L_2 | lead2      | lead2     | Lead2 Account | Software Developer | lead2.sugar@example.org (primary) |

    # Perform records merge with marking specified fields from secondary record to be primary prior to the merge
    When I perform merge of Leads [*L_2, *L_1] records with the following updates:
      | fieldName  |
      | first_name |
      | last_name  |
      | title      |
      | email      |

    # Verify that records are successfully merged
    Then I should not see [*L_1] on Leads list view
    And Leads *L_2 should have the following values:
      | fieldName    | value                   |
      | name         | lead1 lead1             |
      | account_name | Lead2 Account           |
      | email        | lead1.sugar@example.org |
      | title        | Software Engineer       |


  Scenario: Leads > List View > Merge > Change some fields from secondary record to be primary fields > Cancel
    Given Leads records exist:
      | *   | first_name | last_name | account_name  | title              | email                             |
      | L_1 | lead1      | lead1     | Lead1 Account | Software Engineer  | lead1.sugar@example.org (primary) |
      | L_2 | lead2      | lead2     | Lead2 Account | Software Developer | lead2.sugar@example.org (primary) |

    # Perform records merge with marking some fields from secondary record to be primary fields prior to proceeding with the merge
    When I cancel merge of Leads [*L_2, *L_1] records with the following updates:
      | fieldName  |
      | first_name |
      | last_name  |
      | title      |
      | email      |

    # Verify that records are not changed
    Then Leads *L_2 should have the following values:
      | fieldName    | value                   |
      | name         | lead2 lead2             |
      | account_name | Lead2 Account           |
      | email        | lead2.sugar@example.org |
      | title        | Software Developer      |

    Then Leads *L_1 should have the following values:
      | fieldName    | value                   |
      | name         | lead1 lead1             |
      | account_name | Lead1 Account           |
      | email        | lead1.sugar@example.org |
      | title        | Software Engineer       |


  Scenario Outline: Leads > List View > Merge > Verify properties of the error message in case number of merged records is invalid
    Given <numOfRecords> Leads records exist:
      | *           | first_name    | last_name     | account_name          | title             | email                                     |
      | L_{{index}} | lead{{index}} | lead{{index}} | Lead{{index}} Account | Software Engineer | lead{{index}}.sugar@example.org (primary) |

    # Try to merge invalid number of records
    When I perform out of range merge of Leads <recordsToSelect> records

    # Verify properties of the displayed alert
    Then I verify and close alert
      | type  | message                                                                         |
      | Error | Error Invalid number of records passed. The valid range is from 2 to 5 records. |
    Examples:
      | numOfRecords | recordsToSelect                      |
      | 1            | [*L_1]                               |
      | 6            | [*L_1, *L_2, *L_3, *L_4, *L_5, *L_6] |


  Scenario Outline: Leads > List View > Remove record from selected merged records in the merge screen immediately prior to the merge
    Given <numOfRecords> Leads records exist:
      | *           | first_name    | last_name     | account_name          | title             | email                                     |
      | L_{{index}} | lead{{index}} | lead{{index}} | Lead{{index}} Account | Engineer{{index}} | lead{{index}}.sugar@example.org (primary) |

    # Try to merge invalid number of records
    When I remove <record> record before merge of Leads <recordsToSelect> records

    # Verify that record is removed from the list of records to merge and remaining selected records are successfully merged
    Then I should not see [*L_3, *L_4] on Leads list view
    And Leads *L_1 should have the following values:
      | fieldName    | value                   |
      | name         | lead1 lead1             |
      | account_name | Lead1 Account           |
      | email        | lead1.sugar@example.org |
      | title        | Engineer1               |
    And Leads *L_2 should have the following values:
      | fieldName    | value                   |
      | name         | lead2 lead2             |
      | account_name | Lead2 Account           |
      | email        | lead2.sugar@example.org |
      | title        | Engineer2               |

    Examples:
      | numOfRecords | recordsToSelect          | record    |
      | 4            | [*L_1, *L_2, *L_3, *L_4] | primary   |
      | 4            | [*L_1, *L_2, *L_3, *L_4] | secondary |

