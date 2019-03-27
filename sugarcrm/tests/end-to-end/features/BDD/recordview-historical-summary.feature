# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@historical_summary @job6
Feature: Historical Summary

  Background:
    Given I am logged in

  @historical_summary
  Scenario: Leads > Historical Summary
    Given Leads records exist:
      | *   | first_name | last_name | account_name  | title             | email                             |
      | L_1 | lead1      | lead1     | Lead1 Account | Software Engineer | lead1.sugar@example.org (primary) |

    And Calls records exist related via calls link to *L_1:
      | *    | name                          | assigned_user_id | date_start                | duration_hours | duration_minutes | direction | description        | status |
      | Ca_1 | Introductory call to customer | 1                | 2020-04-16T14:30:00-07:00 | 0              | 45               | Outbound  | Initial Call       | Held   |
      | Ca_2 | Followup call to customer     | 1                | 2020-04-16T14:30:00-07:00 | 0              | 45               | Outbound  | Follow-up customer | Planed |

    And Meetings records exist related via meetings link to *L_1:
      | *    | name                           | assigned_user_id | date_start                | duration_minutes | reminder_time | email_reminder_time | description                    | status  |
      | Me_1 | First Meeting with customer    | 1                | 2020-04-16T14:30:00-07:00 | 45               | 0             | 0                   | Initial Meeting with customer  | Held    |
      | Me_2 | Followup Meeting with customer | 1                | 2020-04-16T14:30:00-07:00 | 45               | 0             | 0                   | Followup Meeting with customer | Planned |

    And Tasks records exist related via tasks link to *L_1:
      | *    | name                                 | status        | priority |
      | Ta_1 | Research on Customer's business case | Completed     | High     |
      | Ta_2 | Create business proposal             | Pending Input | High     |

    And Notes records exist related via notes link to *L_1:
      | *    | name                             | description                      | tag          |
      | No_1 | Prepare for the followup call    | Prepair for the followup call    | New Customer |
      | No_2 | Prepare for the followup meeting | Prepair for the followup meeting | New Customer |

    Given I choose Leads in modules menu
    And I select *L_1 in #LeadsList.ListView

    # Open Historical Summary
    When I open actions menu in #L_1Record
    And I choose HistoricalSummary from actions menu in #L_1Record
    Then I should see #LeadsHistoricalSummary view

    # Verify that records with incomplete status don't appear in Historical Summary
    Then I should not see *Ca_2 in #LeadsHistoricalSummary.ListView
    And I should not see *Me_2 in #LeadsHistoricalSummary.ListView
    And I should not see *Ta_2 in #LeadsHistoricalSummary.ListView

    # Verify that records with status is complete appear in the Historical Summary
    Then I should see *Ca_1 in #LeadsHistoricalSummary.ListView
    And I should see *Me_1 in #LeadsHistoricalSummary.ListView
    And I should see *Ta_1 in #LeadsHistoricalSummary.ListView
    And I should see *No_1 in #LeadsHistoricalSummary.ListView
    And I should see *No_2 in #LeadsHistoricalSummary.ListView

    # Click on Preview button and verify data in the preview
    When I click on preview button on *Ca_1 in #LeadsHistoricalSummary.ListView
    Then I verify fields on #Ca_1Preview.PreviewView
      | fieldName | value                         |
      | name      | Introductory call to customer |

    When I click on preview button on *Me_1 in #LeadsHistoricalSummary.ListView
    Then I verify fields on #Me_1Preview.PreviewView
      | fieldName | value                       |
      | name      | First Meeting with customer |

    When I click on preview button on *Ta_1 in #LeadsHistoricalSummary.ListView
    Then I verify fields on #Ta_1Preview.PreviewView
      | fieldName | value                                |
      | name      | Research on Customer's business case |

    When I click on preview button on *No_1 in #LeadsHistoricalSummary.ListView
    Then I verify fields on #No_1Preview.PreviewView
      | fieldName | value                         |
      | name      | Prepare for the followup call |

    When I click on preview button on *No_2 in #LeadsHistoricalSummary.ListView
    Then I verify fields on #No_2Preview.PreviewView
      | fieldName | value                            |
      | name      | Prepare for the followup meeting |

    # Close Historical Summary
    When I click Cancel button on #LeadsHistoricalSummary header
