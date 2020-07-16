# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@crud_modules_shift @job8
Feature: Shifts module verification

  Background:
    Given I am logged in

  @list @preview
  Scenario: Shift > List View > Preview
    Given Shifts records exist:
      | *       | name        | timezone            | is_open_sunday | sunday_open_hour | sunday_open_minutes | sunday_close_hour | sunday_close_minutes | date_start                | date_end                  |
      | Shift_1 | Sally Shift | America/Los_Angeles | true           | 08               | 0                   | 15                | 0                    | 2020-10-19T19:20:22+00:00 | 2020-10-29T19:20:22+00:00 |

    Then Shifts *Shift_1 should have the following values in the list view:
      | fieldName | value               |
      | name      | Sally Shift         |
      | timezone  | America/Los_Angeles |

    Then Shifts *Shift_1 should have the following values in the preview:
      | fieldName      | value               |
      | name           | Sally Shift         |
      | timezone       | America/Los_Angeles |
      | is_open_sunday | true                |
      | sunday_open    | 08:00am             |
      | sunday_close   | 03:00pm             |
      | is_open_monday | true                |
      | monday_open    | 12:00am             |
      | monday_close   | 12:00am             |
      | date_start     | 10/19/2020          |
      | date_end       | 10/29/2020          |


  @list-search
  Scenario: Shift > List View > Filter > Search main input
    Given 3 Shifts records exist:
      | *               | name                 | timezone            | is_open_sunday | sunday_open_hour | sunday_open_minutes | sunday_close_hour | sunday_close_minutes | date_start                | date_end                  |
      | Shift_{{index}} | SallyShift_{{index}} | America/Los_Angeles | true           | 08               | 0                   | 15                | 0                    | 2020-10-19T19:20:22+00:00 | 2020-10-29T19:20:22+00:00 |
    # Search for specific record
    When I choose Shifts in modules menu
    And I search for "SallyShift_2" in #ShiftsList.FilterView view

    # Verify that filtering is successful
    Then I should see [*Shift_2] on Shifts list view
    And I should not see [*Shift_1, *Shift_3] on Shifts list view

  @list-edit
  Scenario Outline: Shifts > List View > Inline Edit > Cancel/Save
    Given Shifts records exist:
      | *       | name        | timezone      | is_open_sunday | sunday_open_hour | sunday_open_minutes | sunday_close_hour | sunday_close_minutes | date_start     | date_end     |
      | Shift_1 | Sally Shift | <orgTimeZone> | true           | 08               | 0                   | 15                | 0                    | <orgStartDate> | <orgEndDate> |

    # Edit record in the list view and Save
    When I edit *Shift_1 record in Shifts list view with the following values:
      | fieldName  | value          |
      | name       | Sally Shift    |
      | timezone   | <newTimeZone>  |
      | date_start | <newStartDate> |
      | date_end   | <newEndDate>   |

    # Verify that editing record is successful
    Then Shifts *Shift_1 should have the following values in the list view:
      | fieldName  | value          |
      | name       | Sally Shift    |
      | timezone   | <newTimeZone>  |
      | date_start | <newStartDate> |
      | date_end   | <newEndDate>   |

    # Edit record in the list view and Cancel
    When I cancel editing of *Shift_1 record in Shifts list view with the following values:
      | fieldName  | value          |
      | name       | Max Shift      |
      | timezone   | <orgTimeZone>  |
      | date_start | <orgStartDate> |
      | date_end   | <orgEndDate>   |

    # Verify that editing record is successful
    Then Shifts *Shift_1 should have the following values in the list view:
      | fieldName  | value          |
      | name       | Sally Shift    |
      | timezone   | <newTimeZone>  |
      | date_start | <newStartDate> |
      | date_end   | <newEndDate>   |

    Examples:
      | orgTimeZone         | newTimeZone    | orgStartDate              | newStartDate | orgEndDate                | newEndDate |
      | America/Los_Angeles | Africa/Algiers | 2020-10-19T19:20:22+00:00 | 11/19/2020   | 2020-10-29T19:20:22+00:00 | 11/29/2020 |


  @list-delete
  Scenario Outline: Shifts > List View > Delete > OK/Cancel
    Given Shifts records exist:
      | *       | name        | timezone            | is_open_sunday | sunday_open_hour | sunday_open_minutes | sunday_close_hour | sunday_close_minutes | date_start                | date_end                  |
      | Shift_1 | Sally Shift | America/Los_Angeles | true           | 08               | 0                   | 15                | 0                    | 2020-10-19T19:20:22+00:00 | 2020-10-29T19:20:22+00:00 |

    # Delete (or Cancel deletion of) record from list view
    When I <action> *Shift_1 record in Shifts list view

    # Verify that record is (is not) deleted
    Then I <expected> see [*Shift_1] on Shifts list view
    Examples:
      | action             | expected   |
      | delete             | should not |
      | cancel deletion of | should     |

  @delete
  Scenario Outline: Shifts > Record View > Delete
    Given Shifts records exist:
      | *       | name        | timezone            | is_open_sunday | sunday_open_hour | sunday_open_minutes | sunday_close_hour | sunday_close_minutes | date_start                | date_end                  |
      | Shift_1 | Sally Shift | America/Los_Angeles | true           | 08               | 0                   | 15                | 0                    | 2020-10-19T19:20:22+00:00 | 2020-10-29T19:20:22+00:00 |

    # Delete (or Cancel deletion of) record in the record view
    When I <action> *Shift_1 record in Shifts record view

    # Verify that record is (is not) deleted
    When I choose Shifts in modules menu
    Then I <expected> see [*Shift_1] on Shifts list view
    Examples:
      | action             | expected   |
      | delete             | should not |
      | cancel deletion of | should     |


  @copy
  Scenario Outline: Shifts > Record View > Copy > Save/Cancel
    Given Shifts records exist:
      | *       | name          | timezone            | is_open_sunday | sunday_open_hour | sunday_open_minutes | sunday_close_hour | sunday_close_minutes | date_start                | date_end                  |
      | Shift_1 | Sally Shift_1 | America/Los_Angeles | true           | 08               | 0                   | 15                | 0                    | 2020-10-19T19:20:22+00:00 | 2020-10-29T19:20:22+00:00 |

    # Copy (or cancel copy of) record in the record view
    When I <action> *Shift_1 record in Shifts record view with the following header values:
      | *       | name                      |
      | Shift_2 | Sally Shift_<changeIndex> |

    # Verify if copy is (is not) created
    Then Shifts *Shift_<expectedIndex> should have the following values:
      | fieldName | value                       |
      | name      | Sally Shift_<expectedIndex> |

    Examples:
      | action         | changeIndex | expectedIndex |
      | cancel copy of | 2           | 1             |
      | copy           | 2           | 2             |


  @create
  Scenario: Shift > Create > Cancel/Save
    When I choose Shifts in modules menu
    # Create Shift > Cancel
    When I click Create button on #ShiftsList header
    When I provide input for #ShiftsDrawer.HeaderView view
      | *       | name        |
      | Shift_1 | Sally Shift |
    When I provide input for #ShiftsDrawer.RecordView view
      | *       | timezone            | is_open_sunday | sunday_open | sunday_close | date_start | date_end   |
      | Shift_1 | America/Los_Angeles | Open           | 08:00am     | 03:00pm      | 10/19/2020 | 10/29/2020 |
    When I click Cancel button on #ShiftsDrawer header
    Then I should see #ShiftsList.ListView view
    When I verify number of records in #ShiftsList.ListView is 0

    # Create Shift > Save
    When I click Create button on #ShiftsList header
    When I provide input for #ShiftsDrawer.HeaderView view
      | *       | name        |
      | Shift_1 | Sally Shift |
    When I provide input for #ShiftsDrawer.RecordView view
      | *       | timezone            | is_open_sunday | sunday_open | sunday_close | date_start | date_end   |
      | Shift_1 | America/Los_Angeles | Open           | 08:00am     | 03:00pm      | 10/19/2020 | 10/29/2020 |
    When I click Save button on #ShiftsDrawer header
    When I close alert
    Then I should see #ShiftsList.ListView view
    Then I verify fields for *Shift_1 in #ShiftsList.ListView
      | fieldName | value       |
      | name      | Sally Shift |
    When I click on preview button on *Shift_1 in #ShiftsList.ListView
    Then I should see #Shift_1Preview view
    When I click show more button on #Shift_1Preview view
    Then I verify fields on #Shift_1Preview.PreviewView
      | fieldName      | value               |
      | name           | Sally Shift         |
      | timezone       | America/Los_Angeles |
      | is_open_sunday | true                |
      | sunday_open    | 08:00am             |
      | sunday_close   | 03:00pm             |
      | is_open_monday | true                |
      | monday_open    | 08:00am             |
      | monday_close   | 05:00pm             |
      | date_start     | 10/19/2020          |
      | date_end       | 10/29/2020          |
