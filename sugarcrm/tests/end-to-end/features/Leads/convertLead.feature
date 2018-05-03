# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@leads @pr
Feature: Leads module verification

  Background:
    Given I use default account
    Given I launch App

    # TITLE:  Verify that Lead Conversion Process is functional
    #
    # STEPS:
    # 1. Generate required records
        # 1.1 Generate Lead record
        # 1.2 Generate Meeting record linked to the lead
        # 1.3 Generate Call record linked to the lead
    # 2. Verify that Calls and Meetings subpanels display related records
    # 3. Verify that label in the lead's header says 'Unconverted'
    # 4. Initiate Lead Conversion process > Cancel
        # 4.1 Generate ID for contact record
        # 4.2 Create Account Record
        # 4.3 Create Opportunity record
    # 5. Cancel Lead conversion
    # 6. Verify that label in the lead's header says 'Unconverted'
    # 7. Initiate Lead Conversion process > Save and Convert
        # 7.1 Generate ID for contact record
        # 7.2 Create Account Record
        # 7.3 Create Opportunity record
        # 7.4 Reset Account record content
        # 7.5 Provide new Account content
        # 7.6 Update opportunity information
    # 8. Finish lead conversion process
    # 9. User is taken back to Lead record view
    # 10. Verify that label in the lead's header says 'Converted'
    # 11. Preview created account record
    # 12. Navigate to generated contact's record view by clicking the link at the bottom of lead record view
    # 13. Verify information of the Contact Record created by lead conversion
    # 14. Verify field(s) in Leads subpanel of Contact record view
    # 15. Verify field(s) in Opportunities subpanel of Contact record view
    # 16. Verify that Meetings and Calls subpanels are empty in Contact record view
    # 17. Verify information of the Account Record created by lead conversion
    # 18. Verify that Meetings and Calls subpanels are empty in Account record view
    # 19. Verify information of the Opportunity Record created by lead conversion
    # 20. Verify that Meetings and Calls subpanels are empty in Opportunity record view

  @lead_conversion
  Scenario: Leads > Convert Lead
    # 1.1 Generate Lead record
    Given Leads records exist:
      | *    | first_name | last_name | title  | phone_mobile   | phone_work     | primary_address  | primary_address_city | primary_address_state | primary_address_postalcode | email                      |
      | John | Elton      | John      | Singer | (746) 079-5067 | (408) 536-6312 | 10050 N Wolfe Rd | Cupertino            | California            | 95014                      | John@example.org (primary) |

    # 1.2 Generate Meeting record linked to the lead
    Given Meetings records exist related via meetings link to *John:
      | *name | assigned_user_id | date_start                | duration_minutes | reminder_time | email_reminder_time | description           | status  |
      | M1    | 1                | 2020-04-16T14:30:00-07:00 | 45               | 0             | 0                   | Meeting with customer | Planned |

    # 1.3 Generate Call record linked to the lead
    Given Calls records exist related via calls link to *John:
      | *name | assigned_user_id | date_start                | duration_minutes | reminder_time | email_reminder_time | description   | status  |
      | Ca1   | 1                | 2020-04-16T14:30:00-07:00 | 45               | 0             | 0                   | Call customer | Planned |

    Given I open about view and login
    When I choose Leads in modules menu
    When I select *John in #LeadsList.ListView
    Then I should see #JohnRecord view

    # 2. Verify that Calls and Meetings subpanels display related records
    When I open the meetings subpanel on #JohnRecord view
    Then I verify fields for *M1 in #JohnRecord.SubpanelsLayout.subpanels.meetings
      | fieldName | value |
      | name      | M1    |

    When I open the calls subpanel on #JohnRecord view
    Then I verify fields for *Ca1 in #JohnRecord.SubpanelsLayout.subpanels.calls
      | fieldName | value |
      | name      | Ca1   |

    # 3. Verify that label in the lead's header says 'Unconverted'
    Then I verify fields on #JohnRecord.HeaderView
      | fieldName | value       |
      | converted | Unconverted |

    # 4. Initiate Lead Conversion process > Cancel
    When I open actions menu in #JohnRecord
    When I choose Convert from actions menu in #JohnRecord

    # 4.1 Generate ID for Contact record
    When I provide input for #JohnLeadConversionDrawer.ContactContent view
      | *  |
      | C1 |

    # 4.2 Create Account Record
    When I provide input for #JohnLeadConversionDrawer.AccountContent view
      | *  | name        |
      | A1 | New Account |
    When I click CreateRecord button on #LeadConversionDrawer.AccountContent

    # 4.3 Create Opportunity record
    When I provide input for #JohnLeadConversionDrawer.OpportunityContent view
      | *  | name            |
      | O1 | New Opportunity |
    When I click CreateRecord button on #LeadConversionDrawer.OpportunityContent

    # 5. Cancel Lead conversion
    When I click Cancel button on #LeadConversionDrawer header

    # 6. Verify that label in the lead's header says 'Unconverted'
    Then I verify fields on #JohnRecord.HeaderView
      | fieldName | value       |
      | converted | Unconverted |

    # 7. Initiate Lead Conversion process > Save and Convert
    When I open actions menu in #JohnRecord
    When I choose Convert from actions menu in #JohnRecord

    # 7.1 Generate ID for Contact record
    When I provide input for #JohnLeadConversionDrawer.ContactContent view
      | *  |
      | C1 |

    # 7.2 Create Account Record
    When I provide input for #JohnLeadConversionDrawer.AccountContent view
      | *  | name        |
      | A1 | New Account |
    When I click CreateRecord button on #LeadConversionDrawer.AccountContent

    # 7.3 Create Opportunity record
    When I provide input for #JohnLeadConversionDrawer.OpportunityContent view
      | *  | name            |
      | O1 | New Opportunity |
    When I click CreateRecord button on #LeadConversionDrawer.OpportunityContent

    # 7.4 Reset Account record content
    When I click Reset button on #LeadConversionDrawer.AccountContent

    # 7.5 Provide new Account content
    When I provide input for #JohnLeadConversionDrawer.AccountContent view
      | *  | name         | website          | industry |
      | A1 | Test Account | www.SugarCrm.com | Banking  |
    When I click CreateRecord button on #LeadConversionDrawer.AccountContent

    # 7.6 Update opportunity information
    When I provide input for #JohnLeadConversionDrawer.OpportunityContent view
      | *  | name                  |
      | O1 | Wonderful Opportunity |
    When I click CreateRecord button on #LeadConversionDrawer.OpportunityContent

    # 8. Finish lead conversion process
    When I click Save button on #LeadConversionDrawer header
    When I close alert

    # 9. User is taken back to Lead record view
    Then I should see #JohnRecord view

    # 10. Verify that label in the lead's header says 'Converted'
    Then I verify fields on #JohnRecord.HeaderView
      | fieldName | value     |
      | converted | Converted |

    # 11. Preview created account record and verify some fields
    When I preview *A1 record on #JohnRecord
    Then I verify fields on #A1Preview.PreviewView
      | fieldName | value                   |
      | name      | Test Account            |
      | website   | http://www.SugarCrm.com |
      | industry  | Banking                 |

    # 12. Navigate to generated contact's record view by clicking the link at the bottom of lead record view
    When I click *C1 record on #JohnRecord
    Then I should see #C1Record view

    # 13. Verify information of the Contact Record created by lead conversion
    When I click show more button on #C1Record view
    Then I verify fields on #C1Record.HeaderView
      | fieldName | value      |
      | name      | Elton John |
    Then I verify fields on #ContactsRecord.RecordView
      | fieldName    | value            |
      | email        | John@example.org |
      | title        | Singer           |
      | phone_mobile | (746) 079-5067   |
      | account_name | Test Account     |

    # 14. Verify field(s) in Leads subpanel of Contact record view
    When I open the leads subpanel on #C1Record view
    Then I verify fields for *John in #C1Record.SubpanelsLayout.subpanels.leads
      | fieldName | value      |
      | name      | Elton John |

    # 15. Verify field(s) in Opportunities subpanel of Contact record view
    When I open the opportunities subpanel on #C1Record view
    Then I verify fields for *O1 in #C1Record.SubpanelsLayout.subpanels.opportunities
      | fieldName | value                 |
      | name      | Wonderful Opportunity |

    # 16 Verify that Meetings and Calls subpanels are empty in Contact record view
    When I open the meetings subpanel on #C1Record view
    Then I verify number of records in #C1Record.SubpanelsLayout.subpanels.meetings is 0

    When I open the calls subpanel on #C1Record view
    Then I verify number of records in #C1Record.SubpanelsLayout.subpanels.meetings is 0

    # 17. Verify information of the Account Record created by lead conversion
    When I choose Accounts in modules menu
    Then I should see *A1 in #AccountsList.ListView
    Then I verify fields for *A1 in #AccountsList.ListView
      | fieldName | value        |
      | name      | Test Account |

    # 18. Verify that Meetings and Calls subpanels are empty in Account record view
    When I select *A1 in #AccountsList.ListView
    Then I should see #A1Record view

    When I open the meetings subpanel on #A1Record view
    Then I verify number of records in #A1Record.SubpanelsLayout.subpanels.meetings is 0

    When I open the calls subpanel on #A1Record view
    Then I verify number of records in #A1Record.SubpanelsLayout.subpanels.meetings is 0


    # 19. Verify information of the Opportunity Record created by lead conversion
    When I choose Opportunities in modules menu
    Then I should see *O1 in #OpportunitiesList.ListView
    Then I verify fields for *O1 in #OpportunitiesList.ListView
      | fieldName    | value                 |
      | name         | Wonderful Opportunity |
      | account_name | Test Account          |

    # 20. Verify that Meetings and Calls subpanels are empty in Opportunity record view
    When I select *O1 in #OpportunitiesList.ListView
    Then I should see #O1Record view

    When I open the meetings subpanel on #O1Record view
    Then I verify number of records in #O1Record.SubpanelsLayout.subpanels.meetings is 0

    When I open the calls subpanel on #O1Record view
    Then I verify number of records in #O1Record.SubpanelsLayout.subpanels.meetings is 0
