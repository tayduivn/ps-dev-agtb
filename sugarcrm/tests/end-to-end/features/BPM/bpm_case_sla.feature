@bpm @job6 @pr
Feature: Case SLA verification

  Background:
    Given I am logged in

  @user_profile
  Scenario: User Profile > Change license type
    When I choose Profile in the user actions menu
    # Change the value of License Type field
    When I change "LicenseTypes[]" enum-user-pref with "Sugar Serve" value in #UserProfile
    When I click on Save button on #UserProfile
    # Verify current value(s) of License Type field
    Then I verify value of "LicenseTypes[]" enum-user-pref field in #UserProfile
      | value       |
      | Sugar Serve |
    When I click on Cancel button on #UserProfile

  @service_console
  Scenario: Case SLA verification

    # Create an Account record
    Given Accounts records exist:
      | *   | name      | service_level | assigned_user_id |
      | A_1 | Account_1 | T1            | 1                |

    # Create a Business Center record
    Given BusinessCenters records exist:
      | *    | name       | timezone            | is_open_sunday | sunday_open_hour | sunday_open_minutes | sunday_close_hour | sunday_close_minutes | is_open_monday | monday_open_hour | monday_open_minutes | monday_close_hour | monday_close_minutes | is_open_tuesday | tuesday_open_hour | tuesday_open_minutes | tuesday_close_hour | tuesday_close_minutes | is_open_wednesday | wednesday_open_hour | wednesday_open_minutes | wednesday_close_hour | wednesday_close_minutes | is_open_thursday | thursday_open_hour | thursday_open_minutes | thursday_close_hour | thursday_close_minutes | assigned_user_id |
      | BC_1 | West Coast | America/Los_Angeles | true           | 09               | 0                   | 17                | 0                    | true           | 09               | 0                   | 17                | 0                    | true            | 09                | 0                    | 17                 | 0                     | true              | 09                  | 0                      | 17                   | 0                       | true             | 09                 | 0                     | 17                  | 0                      | 1                |

    Then BusinessCenters *BC_1 should have the following values in the list view:
      | fieldName | value               |
      | name      | West Coast          |
      | timezone  | America/Los_Angeles |

    # Go to Process definition -> Import Process Definition
    When I choose pmse_Project in modules menu and select "Import Process Definitions" menu item

    # Import bpm file and assign record identifier to the imported record
    When I create a new record *BP_1 by importing Business Rules from "Case_SLA_Updating.bpm" file on #pmse_ProjectRecord.ImportBpmView
    When I close alert

    # Verify that Business Process module list view displays imported record
    Then I verify number of records in #pmse_ProjectList.ListView is 6

    # Enable process
    When I click on Enable button for *BP_1 in #pmse_ProjectList.ListView
    When I Confirm confirmation alert
    When I close alert

    # Create a Case record with high priority
    When I choose Cases in modules menu
    When I click Create button on #CasesList header
    When I provide input for #CasesDrawer.HeaderView view
      | *  | name   |
      | C1 | Case_1 |
    When I provide input for #CasesDrawer.RecordView view
      | account_name | priority | business_center_name | resolved_datetime  |
      | Account_1    | High     | West Coast           | 10/29/2019-12:00pm |
    When I click Save button on #CasesDrawer header
    When I close alert
    Then I should see #CasesList.ListView view
    Then I should see *C1 in #CasesList.ListView
    When I click on preview button on *C1 in #CasesList.ListView
    Then I verify fields on #C1Preview.PreviewView
      | fieldName            | value              |
      | account_name         | Account_1          |
      | priority             | High               |
      | business_center_name | West Coast         |
      | follow_up_datetime   | 10/29/2019 04:00pm |

    # Create a Case record with medium priority
    When I click Create button on #CasesList header
    When I provide input for #CasesDrawer.HeaderView view
      | *  | name   |
      | C2 | Case_2 |
    When I provide input for #CasesDrawer.RecordView view
      | account_name | priority | business_center_name | resolved_datetime  |
      | Account_1    | Medium   | West Coast           | 10/29/2019-12:00pm |
    When I click Save button on #CasesDrawer header
    When I close alert
    Then I should see #CasesList.ListView view
    Then I should see *C2 in #CasesList.ListView
    When I click on preview button on *C2 in #CasesList.ListView
    Then I verify fields on #C2Preview.PreviewView
      | fieldName            | value              |
      | follow_up_datetime   | 10/30/2019 12:00pm |

    # Create a Case record with low priority
    When I click Create button on #CasesList header
    When I provide input for #CasesDrawer.HeaderView view
      | *  | name   |
      | C3 | Case_3 |
    When I provide input for #CasesDrawer.RecordView view
      | account_name | priority | business_center_name | resolved_datetime  |
      | Account_1    | Low      | West Coast           | 10/29/2019-12:00pm |
    When I click Save button on #CasesDrawer header
    When I close alert
    Then I should see #CasesList.ListView view
    Then I should see *C3 in #CasesList.ListView
    When I click on preview button on *C3 in #CasesList.ListView
    Then I verify fields on #C3Preview.PreviewView
      | fieldName            | value              |
      | follow_up_datetime   | 10/31/2019 12:00pm |

  @user_profile
  Scenario: User Profile > Change License type
    When I choose Profile in the user actions menu
    # Change value of License Type field
    When I change "LicenseTypes[]" enum-user-pref with "Sugar Enterprise" value in #UserProfile
    When I click on Save button on #UserProfile
    # Verify current value(s) of License Type field
    Then I verify value of "LicenseTypes[]" enum-user-pref field in #UserProfile
      | value            |
      | Sugar Enterprise |
    When I click on Cancel button on #UserProfile
