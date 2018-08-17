# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

@admin @job6
Feature: Admin

  Background:
    Given I use default account
    Given I launch App

  @change_system_settings @pr
  Scenario: Admin > Change System Settings
    When I open about view and login
    When I go to "bwc/index.php?module=Administration" url
    When I click on SystemSettings link in #AdminPanel
    # Change lead conversion settings
    When I set lead_conv_activity_opt enum with "Do Nothing" value on #AdminPanel:SystemSettings
    When I click on Save button on #AdminPanel:SystemSettings


  @user_profile @pr
  Scenario Outline: User Profile > Activate License
    When I open Accounts view and login
    When I choose Profile in the user actions menu
    # Verify current value(s) of License Type field
    Then I verify value of "LicenseTypes[]" enum-user-pref field in #UserProfile
      | value |
      | <ENT> |
    When I click on Cancel button on #UserProfile
    # Change the value of License Type field
    When I change "LicenseTypes[]" enum-user-pref with "Sugar Serve" value in #UserProfile
    When I click on Save button on #UserProfile
    # Verify current value(s) of License Type field
    Then I verify value of "LicenseTypes[]" enum-user-pref field in #UserProfile
      | value          |
      | <ENT>, <SERVE> |
    When I click on Cancel button on #UserProfile

    Examples:
      | ENT              | SERVE       |
      | Sugar Enterprise | Sugar Serve |

  @user_profile @pr
  Scenario: User Profile > Change tabs
    When I open Accounts view and login
    When I choose Profile in the user actions menu
    When I select Advanced tab in #UserProfile
    When I click on Cancel button on #UserProfile
