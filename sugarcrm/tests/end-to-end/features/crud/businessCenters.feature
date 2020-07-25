# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@crud_modules_business_center @job4 @ent-only
Feature: Business Center module verification

  Background:
    Given I am logged in


  @user_profile
  Scenario: User Profile > Update 'License Types' field
    When I choose Profile in the user actions menu
    # Change the value of License Type field
    When I change "LicenseTypes[]" enum-user-pref with "Sugar Serve" value in #UserProfile
    When I click on Save button on #UserProfile
    # Verify current value(s) of License Type field
    Then I verify value of "LicenseTypes[]" enum-user-pref field in #UserProfile
      | value                         |
      | Sugar Enterprise, Sugar Serve |
    When I click on Cancel button on #UserProfile
    When I choose Accounts in modules menu


  @list @preview
  Scenario: Business Center > List View > Preview
    Given BusinessCenters records exist:
      | *    | name       | timezone            | is_open_sunday | sunday_open_hour | sunday_open_minutes | sunday_close_hour | sunday_close_minutes | address_street   | address_city | address_state | address_postalcode | address_country |
      | BC_1 | West Coast | America/Los_Angeles | true           | 08               | 0                   | 15                | 0                    | 10050 N Wolfe Rd | Cupertino    | California    | 95014              | USA             |

    Then BusinessCenters *BC_1 should have the following values in the list view:
      | fieldName | value               |
      | name      | West Coast          |
      | timezone  | America/Los_Angeles |

    Then BusinessCenters *BC_1 should have the following values in the preview:
      | fieldName          | value               |
      | name               | West Coast          |
      | timezone           | America/Los_Angeles |
      | is_open_sunday     | Open                |
      | sunday_open        | 08:00am             |
      | sunday_close       | 03:00pm             |
      | is_open_monday     | Open                |
      | monday_open        | 12:00am             |
      | monday_close       | 12:00am             |
      | address_city       | Cupertino           |
      | address_street     | 10050 N Wolfe Rd    |
      | address_postalcode | 95014               |
      | address_state      | California          |
      | address_country    | USA                 |


  @list-search
  Scenario: Business Center > List View > Filter > Search main input
    Given 3 BusinessCenters records exist:
      | *            | name                | timezone            | is_open_sunday | sunday_open_hour | sunday_open_minutes | sunday_close_hour | sunday_close_minutes |
      | BC_{{index}} | WestCoast_{{index}} | America/Los_Angeles | true           | 08               | 0                   | 15                | 0                    |
    # Search for specific record
    When I choose BusinessCenters in modules menu
    And I search for "WestCoast_2" in #BusinessCentersList.FilterView view

    # Verify that filtering is successful
    Then I should see [*BC_2] on BusinessCenters list view
    And I should not see [*BC_1, *BC_3] on BusinessCenters list view


  @list-edit
  Scenario Outline: Business Centers > List View > Inline Edit > Cancel/Save
    Given BusinessCenters records exist:
      | *    | name       | timezone      | is_open_sunday | sunday_open_hour | sunday_open_minutes | sunday_close_hour | sunday_close_minutes | address_street   | address_city | address_state | address_postalcode | address_country |
      | BC_1 | West Coast | <orgTimeZone> | true           | 08               | 0                   | 15                | 0                    | 10050 N Wolfe Rd | Cupertino    | California    | 95014              | <orgCountry>    |

    # Edit record in the list view and Save
    When I edit *BC_1 record in BusinessCenters list view with the following values:
      | fieldName       | value           |
      | name            | West Coast - LA |
      | timezone        | <newTimeZone>   |
      | address_country | <newCountry>    |

    # Verify that editing record is successful
    Then BusinessCenters *BC_1 should have the following values in the list view:
      | fieldName       | value           |
      | name            | West Coast - LA |
      | timezone        | <newTimeZone>   |
      | address_country | <newCountry>    |

    # Edit record in the list view and Cancel
    When I cancel editing of *BC_1 record in BusinessCenters list view with the following values:
      | fieldName       | value           |
      | name            | West Coast - SF |
      | timezone        | <orgTimeZone>   |
      | address_country | <orgCountry>    |

    # Verify that editing record is successful
    Then BusinessCenters *BC_1 should have the following values in the list view:
      | fieldName       | value           |
      | name            | West Coast - LA |
      | timezone        | <newTimeZone>   |
      | address_country | <newCountry>    |

    Examples:
      | orgTimeZone         | newTimeZone    | orgCountry | newCountry |
      | America/Los_Angeles | Africa/Algiers | USA        | Nigeria    |


  @list-delete
  Scenario Outline: Business Centers > List View > Delete > OK/Cancel
    Given BusinessCenters records exist:
      | *    | name       | timezone        | is_open_sunday | sunday_open_hour | sunday_open_minutes | sunday_close_hour | sunday_close_minutes |
      | BC_1 | East Coast | America/Detroit | true           | 08               | 0                   | 15                | 0                    |
    # Delete (or Cancel deletion of) record from list view
    When I <action> *BC_1 record in BusinessCenters list view
    # Verify that record is (is not) deleted
    Then I <expected> see [*BC_1] on BusinessCenters list view
    Examples:
      | action             | expected   |
      | delete             | should not |
      | cancel deletion of | should     |


  @delete
  Scenario Outline: Business Centers > Record View > Delete
    Given BusinessCenters records exist:
      | *    | name       | timezone        | is_open_sunday | sunday_open_hour | sunday_open_minutes | sunday_close_hour | sunday_close_minutes |
      | BC_1 | East Coast | America/Detroit | true           | 08               | 0                   | 15                | 0                    |

    # Delete (or Cancel deletion of) record in the record view
    When I <action> *BC_1 record in BusinessCenters record view

    # Verify that record is (is not) deleted
    When I choose BusinessCenters in modules menu
    Then I <expected> see [*BC_1] on BusinessCenters list view
    Examples:
      | action             | expected   |
      | delete             | should not |
      | cancel deletion of | should     |


  @copy
  Scenario Outline: Business Centers > Record View > Copy > Save/Cancel
    Given BusinessCenters records exist:
      | *    | name         | timezone        | is_open_sunday | sunday_open_hour | sunday_open_minutes | sunday_close_hour | sunday_close_minutes |
      | BC_1 | East Coast_1 | America/Detroit | true           | 08               | 0                   | 15                | 0                    |

    # Copy (or cancel copy of) record in the record view
    When I <action> *BC_1 record in BusinessCenters record view with the following header values:
      | *    | name                     |
      | BC_2 | East Coast_<changeIndex> |

    # Verify if copy is (is not) created
    Then BusinessCenters *BC_<expectedIndex> should have the following values:
      | fieldName | value                      |
      | name      | East Coast_<expectedIndex> |

    Examples:
      | action         | changeIndex | expectedIndex |
      | cancel copy of | 2           | 1             |
#     Please uncomment the next line after CS-246 is fixed
#     | copy           | 2           | 2             |


  @create
  Scenario: Business Center > Create > Cancel/Save
    When I choose BusinessCenters in modules menu
    # Create Business Center > Cancel
    When I click Create button on #BusinessCentersList header
    When I provide input for #BusinessCentersDrawer.HeaderView view
      | *    | name       |
      | BC_1 | West Coast |
    When I provide input for #BusinessCentersDrawer.RecordView view
      | *    | timezone            | is_open_sunday | sunday_open | sunday_close | address_street   | address_city | address_state | address_postalcode | address_country |
      | BC_1 | America/Los_Angeles | Open           | 08:00am     | 03:00pm      | 10050 N Wolfe Rd | Cupertino    | California    | 95014              | USA             |
    When I click Cancel button on #BusinessCentersDrawer header
    Then I should see #BusinessCentersList.ListView view
    When I verify number of records in #BusinessCentersList.ListView is 0

    # Create Business Center > Save
    When I click Create button on #BusinessCentersList header
    When I provide input for #BusinessCentersDrawer.HeaderView view
      | *    | name       |
      | BC_1 | West Coast |
    When I provide input for #BusinessCentersDrawer.RecordView view
      | *    | timezone            | is_open_sunday | sunday_open | sunday_close | address_street   | address_city | address_state | address_postalcode | address_country |
      | BC_1 | America/Los_Angeles | Open           | 08:00am     | 03:00pm      | 10050 N Wolfe Rd | Cupertino    | California    | 95014              | USA             |
    When I click Save button on #BusinessCentersDrawer header
    When I close alert
    Then I should see #BusinessCentersList.ListView view
    Then I verify fields for *BC_1 in #BusinessCentersList.ListView
      | fieldName | value      |
      | name      | West Coast |
    When I click on preview button on *BC_1 in #BusinessCentersList.ListView
    Then I should see #BC_1Preview view
    When I click show more button on #BC_1Preview view
    Then I verify fields on #BC_1Preview.PreviewView
      | fieldName          | value               |
      | name               | West Coast          |
      | timezone           | America/Los_Angeles |
      | is_open_sunday     | Open                |
      | sunday_open        | 08:00am             |
      | sunday_close       | 03:00pm             |
      | is_open_monday     | Open                |
      | monday_open        | 08:00am             |
      | monday_close       | 05:00pm             |
      | address_city       | Cupertino           |
      | address_street     | 10050 N Wolfe Rd    |
      | address_postalcode | 95014               |
      | address_state      | California          |
      | address_country    | USA                 |


  @user_profile
  Scenario: User Profile > Update 'License Types' field
    When I choose Profile in the user actions menu
    # Change the value of License Type field
    When I change "LicenseTypes[]" enum-user-pref with "Sugar Serve" value in #UserProfile
    When I click on Save button on #UserProfile
    # Verify current value(s) of License Type field
    Then I verify value of "LicenseTypes[]" enum-user-pref field in #UserProfile
      | value            |
      | Sugar Enterprise |
    When I click on Cancel button on #UserProfile
