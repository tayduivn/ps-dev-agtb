# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@share @job4
Feature: Record view > Share

  Background:
    Given I am logged in

  @share
  Scenario Outline: Leads > Share
    Given Leads records exist:
      | *    | first_name | last_name  | account_name  | title             | email                                          |
      | Le_1 | <leadName> | <leadName> | Lead1 Account | Software Engineer | <leadName>.<accountName>@example.org (primary) |
    And Accounts records exist:
      | *    | name          | email                                |
      | Ac_1 | <accountName> | <accountName>@sugarcrm.com (primary) |

    When I open Leads *Le_1 record view
    # Select 'Share' in record view Actions drop-down
    When I open actions menu in #Le_1Record
    And I choose Share from actions menu in #Le_1Record
    # Close warning message
    When I close alert

    # Add email addressee
    When I add the following recipients to the email in #EmailsRecord.RecordView
      | fieldName | value |
      | To        | *Ac_1 |

    # Get email record identifier
    When I provide input for #EmailsDrawer.HeaderView view
      | *    |
      | Em_1 |

    # Save email without any changes
    When I click Save button on #EmailsDrawer header
    When I close alert

    # Verify that draft email record is created successfully in the sub-panel
    When I open the archived_emails subpanel on #Le_1Record view
    Then I verify number of records in #Le_1Record.SubpanelsLayout.subpanels.archived_emails is 1

    # Verify that draft email is created successfully
    Then Emails *Em_1 should have the following values in the preview:
      | fieldName     | value                                                |
      | name          | Shared Lead <leadName> <leadName> from <accountName> |
      | from_collection | Administrator |
      | to_collection | <accountName>                                        |
      | parent_name   | <leadName> <leadName>                                |

    Examples:
      | leadName | accountName |
      | lead1    | SugarCRM    |
