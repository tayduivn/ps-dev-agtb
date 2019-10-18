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

  @user_profile @pr
  Scenario: User Profile > Change tabs
    When I open Accounts view and login
    When I choose Profile in the user actions menu
    When I select Advanced tab in #UserProfile
    When I click on Cancel button on #UserProfile
