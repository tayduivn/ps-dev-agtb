# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@data_privacy
Feature: Data Privacy Consent

    Background:
        Given I use default account
        Given I launch App

        # TITLE: Verify that the Data Privacy Consent flow behaves as expected
        #
        # STEPS:
        # 1.1 Create a Contact record
        # 1.2 Create a Data Privacy "Consent to Process" record with a deadline
        # 1.3 Link the Data Privacy record to the Contact record
        # 1.4 Update the Contact record with Consent data
        # 1.5 Complete the Data Privacy record
        #
        # 2.1 Create a Data Privacy "Withdrawal of Process" record with a deadline
        # 2.2 Link the Data Privacy record to the Contact record
        # 2.3 Update the Contact to remove Consent data
        # 2.4 Complete the Data Privacy record
        #
        # TODO:
        # 1. Support roles and personas, such as Data Privacy Manager
        # 2. Support shortcut navigation to a single record
        # 3. Support enabling of disabled modules


    @DataPrivacy_GrantConsent
    Scenario: Data Privacy > Grant Consent
        Given DataPrivacy records exist:
            | *name | type               | priority | source | date_due                  | business_purpose                    | assigned_user_id |
            | dp01  | Consent to Process | Low      | Phone  | 2020-10-19T19:20:22+00:00 | Marketing Communications by company | 1                |

        Given Contacts records exist:
            | *       | first_name | last_name | email           |
            | joe_s   | Joe        | Shmoe     | a@a.a (primary) |

        Given I open about view and login

        # We have to go the Data Privacy module since it is disabled by default
        # thus forcing us to hit the module URL instead of navigating to the target
        # module and going through the subpanel for linkage
        When I go to "DataPrivacy" url

        # Because of limitations in our testing platform, you cannot simply retrieve
        # a record, you have to navigate to the module list view and select a
        # record from the list
        When I select *dp01 in #DataPrivacyList.ListView

        # This is very UI specific, and ultimately should be changed to just interacting
        # with the record as opposed to having to "see" it in a "view"
        Then I should see #dp01Record view

        # Link the Contact record to Data Privacy record
        When I link existing record *joe_s to contacts subpanel on #dp01Record view

        # Navigate to the Contact record, by way of the Contacts module list view
        When I choose Contacts in modules menu
        When I select *joe_s in #ContactsList.ListView
        Then I should see #joe_sRecord view

        # Update the Contact record to add a Data Privacy Business Purpose
        When I click Edit button on #joe_sRecord header
        When I provide input for #joe_sRecord.RecordView view
            | dp_business_purpose                 | dp_consent_last_updated   |
            | Marketing communications by company | 2020-09-10T19:20:22+00:00 |
        
        # Save the Contact record
        When I click Save button on #joe_sRecord header
        When I close alert
        Then I should see #joe_sRecord view

        # Back to the Data Privacy record via the module list view
        When I go to "DataPrivacy" url
        When I select *dp01 in #DataPrivacyList.ListView
        Then I should see #dp01Record view

        # Complete the Data Privacy record
        When I complete the Data Privacy request on #dp01Record
        Then I Verify fields on #dp01Record.RecordView
            | fieldName | value  |
            | status    | Closed |


    @DataPrivacy_WithdrawConsent
    Scenario: Data Privacy > Withdraw Consent
        Given DataPrivacy records exist:
            | *name | type             | priority  | source | date_due                  | business_purpose                     | resolution           | assigned_user_id |
            | dp02  | Withdraw Consent | High      | Email  | 2020-10-19T19:20:22+00:00 | Marketing Communications by partners | I hate communication | 1                |

        Given Contacts records exist:
            | *     | first_name | last_name | email           |
            | joe_s | Joe        | Shmoe     | a@a.a (primary) |

        Given I open about view and login

        # Open the Data Privacy record to link the Contact record to it
        When I go to "DataPrivacy" url
        When I select *dp02 in #DataPrivacyList.ListView
        Then I should see #dp02Record view

        # Link the Contact record to Data Privacy record
        When I link existing record *joe_s to contacts subpanel on #dp02Record view
        When I go to "Contacts" url
        When I select *joe_s in #ContactsList.ListView
        Then I should see #joe_sRecord view

        # Update the Contact record to add a Data Privacy Business Purpose
        When I click Edit button on #joe_sRecord header
        When I provide input for #joe_sRecord.RecordView view
            | dp_business_purpose | dp_consent_last_updated   |
            |                     | 2020-06-20T19:20:22+00:00 |

        # Save the Contact record
        When I click Save button on #joe_sRecord header
        When I close alert
        Then I should see #joe_sRecord view

        # Back to the Data Privacy record via the module list view
        When I go to "DataPrivacy" url
        When I select *dp01 in #DataPrivacyList.ListView
        Then I should see #dp01Record view

        # Complete the Data Privacy record
        When I complete the Data Privacy request on #dp01Record
        Then I Verify fields on #dp01Record.RecordView
            | fieldName | value  |
            | status    | Closed |
