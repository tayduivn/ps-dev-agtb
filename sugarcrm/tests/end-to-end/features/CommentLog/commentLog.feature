# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@CommentLog
Feature: CommentLog in record view

  Background:
    Given I use default account
    Given I launch App

    @Add_CommentLog_when_one_is_already_there @pr
      Scenario: Comment Log > CommentLog existed in a record > add another CommentLog
        Given Bugs records exist:
        | *     | name                     | date_start                | assigned_user_id |
        | bug1  | Best Sandwich Discussion | 2020-04-16T14:30:00-07:00 | 1                |
        And CommentLog records exist related via commentlog_link link:
          | *     | entry                                                 |
          | log1  | It must be ike's, why are we having this bug anyways? |
      Given I open about view and login
      When I go to "Bugs" url
        When I select *bug1 in #BugsList.ListView
        When I provide input for #bug1Record
          | commentlog                             |
          | Objection! That's why we need this bug |
        Then I verify fields on #bug1Record.RecordView
          | fieldName    | value                                                                                        |
          | commentlog   | It must be ike's, why are we having this bug anyways?,Objection! That's why we need this bug |

    @Add_fresh_CommentLog_to_record @pr
      Scenario: Comment Log > A record with no CommentLog > add a CommentLog
        Given Bugs records exist:
          | *    | name                   | date_start                | assigned_user_id |
          | bug2 | Groot adolescent issue | 2020-04-16T14:30:00-07:00 | 1                |
        Given I open about view and login
        When I go to "Bugs" url
        When I select *bug2 in #BugsList.ListView
        When I provide input for #bug2Record
          | commentlog            |
          | Don't let Groot know! |
        Then I verify fields on #bug2Record.RecordView
          | fieldName    | value                 |
          | commentlog   | Don't let Groot know! |
