# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

# Commented out until Comment Log is added to an OOTB module or we have time to implement adding via studio
# @CommentLog
#Feature: CommentLog in record view
#
#  Background:
#    Given I use default account
#    Given I launch App
#
#    @Add_CommentLog_when_one_is_already_there @pr
#      Scenario: CommentLog existed in a record > add another CommentLog
#        Given Tasks records exist:
#        | *     | name                     | date_start                | assigned_user_id | priority |
#        | task1 | Best Sandwich Discussion | 2020-04-16T14:30:00-07:00 | 1                | High     |
#        And CommentLog records exist related via commentlog_link link:
#          | *     | entry                                                  |
#          | log1  | It must be ike's, why are we having this task anyways? |
#      Given I open about view and login
#      When I choose Tasks in modules menu
#        When I select *task1 in #TasksList.ListView
#        When I provide input for #task1Record
#          | commentlog                                    |
#          | Objection! That's why we need this task     |
#        Then I verify fields on #task1Record.RecordView
#          | fieldName    | value                                                                                          |
#          | commentlog   | It must be ike's, why are we having this task anyways?,Objection! That's why we need this task |
#
#    @Add_fresh_CommentLog_to_record @pr
#      Scenario: A record with no CommentLog > add a CommentLog
#        Given Tasks records exist:
#          | *     | name                   | date_start                | assigned_user_id | priority |
#          | task2 | Groot adolescent issue | 2020-04-16T14:30:00-07:00 | 1                | High     |
#        Given I open about view and login
#        When I choose Tasks in modules menu
#        When I select *task2 in #TasksList.ListView
#        When I provide input for #task2Record
#          | commentlog               |
#          | Don't let Groot know!     |
#        Then I verify fields on #task2Record.RecordView
#          | fieldName     | value                 |
#          | commentlog   | Don't let Groot know! |
#
#    @CommentLog_across_records @pr
#      Scenario: Add CommentLog to multiple different existing records
#        Given Tasks records exist:
#          | *     | name                           | date_start                | assigned_user_id | priority |
#          | task3 | New Avengers Candidate Meeting | 2020-04-16T14:30:00-07:00 | 1                | High     |
#        Given Tasks records exist:
#          | *     | name                       | date_start                | assigned_user_id | priority |
#          | task4 | Justice League Orientation | 2020-05-16T14:30:00-07:00 | 1                | High     |
#        And CommentLog records exist related via commentlog_link link:
#          | *    | entry                                              |
#          | log4 | Tacos will be ordered for task, amount TBD -BatMan |
#        Given I open about view and login
#        When I choose Tasks in modules menu
#        When I select *task3 in #TasksList.ListView
#        When I provide input for #task3Record
#          | commentlog                                             |
#          | I think spider man should be one of us, objections?     |
#        When I choose Tasks in modules menu
#        When I select *task4 in #TasksList.ListView
#        When I provide input for #task4Record
#          | commentlog                                           |
#          | Since we got Flash, I'll just order 850 -- Batman     |
#        When I choose Tasks in modules menu
#        When I select *task3 in #TasksList.ListView
#        Then I verify fields on #task3Record.RecordView
#          | fieldName     | value                                               |
#          | commentlog   | I think spider man should be one of us, objections? |
#        When I choose Tasks in modules menu
#        When I select *task4 in #TasksList.ListView
#        Then I verify fields on #task4Record.RecordView
#          | fieldName    | value                                                                                                |
#          | commentlog   | Tacos will be ordered for task, amount TBD -BatMan,Since we got Flash, I'll just order 850 -- Batman |
