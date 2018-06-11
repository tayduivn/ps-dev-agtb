# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
# Copyright (C) SugarCRM Inc. All rights reserved.

@GlobalSearch
Feature: GlobalSearch
    As a Sugar user, I need to be able to find records in the system through the use of search terms

  Background:
    Given I am logged in

  @api
  Scenario: Searching filtered by module
    Given Employees records exist:
      | first_name | last_name | user_name |
      | Jim        | Brennan   | jim_test  |
    And Contacts records exist:
      | first_name | last_name  |
      | Jerrod     | Brennerman |
      | Brady      | Jimenez    |

      # Because a background step will run for every example and all our examples use the same data set without
      # manipulating it, we should define all the expected behaviors of searching this data set in a single Scenario.
      # An alternative to the below would be to define a step that supports searching a table of data and asserting
      # those results.
      # NEED TO COME UP WITH MORE QUERIES AND GIVENS.

      Then the following queries should have the following results:
      | terms        | modules | total | value   | fieldName  |
      | jim          |         | 2     | Jim     | first_name |
      | jim          |         | 2     | Jimenez | last_name  |
      | bren         |         | 2     | Jerrod  | first_name |
      | jim AND bren |         | 1     | Jim     | first_name |
