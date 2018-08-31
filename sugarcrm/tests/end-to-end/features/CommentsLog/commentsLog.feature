# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@Commentslog
Feature: Commentslog in record view

  Background:
    Given I use default account
    Given I launch App

    @Add_Commentslog_when_one_is_already_there @pr
      Scenario: Commentslog existed in a record > add another Commentslog
        Given Meetings records exist:
        | *        | name                     | commentslog                                                   | date_start                | assigned_user_id |
        | meeting1 | Best Sandwich Discussion | It must be ike's, why are we having this meeting anyways?     | 2020-04-16T14:30:00-07:00 | 1                |
      Given I open about view and login
      When I choose Meetings in modules menu
        When I select *meeting1 in #MeetingsList.ListView
        When I provide input for #meeting1Record
          | commentslog                                    |
          | Objection! That's why we need this meeting     |
        Then I verify fields on #meeting1Record.RecordView
          | fieldName     | value                                                                                                |
          | commentslog   | It must be ike's, why are we having this meeting anyways?,Objection! That's why we need this meeting |

    @Add_fresh_Commentslog_to_record @pr
      Scenario: A record with no Commentslog > add a Commentslog
        Given Meetings records exist:
          | *        | name                   | date_start                | assigned_user_id |
          | meeting2 | Goort adolescent issue | 2020-04-16T14:30:00-07:00 | 1                |
        Given I open about view and login
        When I choose Meetings in modules menu
        When I select *meeting2 in #MeetingsList.ListView
        When I provide input for #meeting2Record
          | commentslog               |
          | Don't let Groot know!     |
        Then I verify fields on #meeting2Record.RecordView
          | fieldName     | value                 |
          | commentslog   | Don't let Groot know! |

    @Commentslog_across_records @pr
      Scenario: Add Commentslog to multiple different existing records
        Given Meetings records exist:
          | *        | name                          | date_start                | assigned_user_id |
          | meeting3 | New Avenger Candidate Meeting | 2020-04-16T14:30:00-07:00 | 1                |
        Given Meetings records exist:
          | *        | name                          | commentslog                                               | date_start                | assigned_user_id |
          | meeting4 | Justice League Orientation    | Tacos will be ordered for meeting, amount TBD -BatMan     | 2020-05-16T14:30:00-07:00 | 1                |
        Given I open about view and login
        When I choose Meetings in modules menu
        When I select *meeting3 in #MeetingsList.ListView
        When I provide input for #meeting3Record
          | commentslog                                             |
          | I think spider man should be one of us, objections?     |
        When I choose Meetings in modules menu
        When I select *meeting4 in #MeetingsList.ListView
        When I provide input for #meeting4Record
          | commentslog                                           |
          | Since we got Flash, I'll just order 850 -- Batman     |
        When I choose Meetings in modules menu
        When I select *meeting3 in #MeetingsList.ListView
        Then I verify fields on #meeting3Record.RecordView
          | fieldName     | value                                               |
          | commentslog   | I think spider man should be one of us, objections? |
        When I choose Meetings in modules menu
        When I select *meeting4 in #MeetingsList.ListView
        Then I verify fields on #meeting4Record.RecordView
          | fieldName     | value                                                                                                   |
          | commentslog   | Tacos will be ordered for meeting, amount TBD -BatMan,Since we got Flash, I'll just order 850 -- Batman |



