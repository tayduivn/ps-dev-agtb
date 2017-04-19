@ldap @login
Feature: LDAP
  New user should not be able to login if provision disabled
  New user should be able to login and see complete registration screen
  User should not be able to login if it does not belong to LDAP group
  User should be able to login if it belongs to LDAP group

  Scenario: New LDAP user try to login if provision is disabled
    Given As "sugarAdmin" filling in the following LDAP settings:
      | field | type | value |
      | ldap_hostname | text | 127.0.0.1 |
      | ldap_port | text | 389 |
      | ldap_base_dn | text |  dc=openldap,dc=com |
      | ldap_login_filter | text | |
      | ldap_bind_attr    | text | dn |
      | ldap_login_attr    | text | uid |
      | ldap_group_checkbox    | checkbox | |
      | ldap_authentication_checkbox    | checkbox | checked |
      | ldap_admin_user    | text | cn=admin,ou=admins,dc=openldap,dc=com |
      | ldap_admin_password    | text | admin |
      | ldap_auto_create_users | checkbox | |
    Given I login as "user1" with password "user1"
    Then I should see "You need to be logged in to perform this action."

  Scenario: New LDAP user tries to login if provision is enabled
    Given As "sugarAdmin" filling in the following LDAP settings:
      | field | type | value |
      | ldap_hostname | text | 127.0.0.1 |
      | ldap_port | text | 389 |
      | ldap_base_dn | text |  dc=openldap,dc=com |
      | ldap_login_filter | text | |
      | ldap_bind_attr    | text | dn |
      | ldap_login_attr    | text | uid |
      | ldap_group_checkbox    | checkbox | |
      | ldap_authentication_checkbox    | checkbox | checked |
      | ldap_admin_user    | text | cn=admin,ou=admins,dc=openldap,dc=com |
      | ldap_admin_password    | text | admin |
      | ldap_auto_create_users | checkbox | checked |
    Given I login as "user1" with password "user1"
    Given I want to delete current user after test
    Then I should see "Setup your user information"
    Then I skip login wizard
    And I logout

  @groups
  Scenario: LDAP user tries to login if it does not belong to group
    Given As "sugarAdmin" filling in the following LDAP settings:
      | field | type | value |
      | ldap_hostname | text | 127.0.0.1 |
      | ldap_port | text | 389 |
      | ldap_base_dn | text |  dc=openldap,dc=com |
      | ldap_login_filter | text | |
      | ldap_bind_attr    | text | dn |
      | ldap_login_attr    | text | uid |
      | ldap_group_checkbox    | checkbox | checked |
      | ldap_authentication_checkbox    | checkbox | checked |
      | ldap_admin_user    | text | cn=admin,ou=admins,dc=openldap,dc=com |
      | ldap_admin_password    | text | admin |
      | ldap_auto_create_users | checkbox | checked |
      | ldap_group_dn | text | ou=groups,dc=openldap,dc=com |
      | ldap_group_name | text | cn=Administrators |
      | ldap_group_attr | text | member |
    Given I login as "user1" with password "user1"
    Then I should see "LDAP user does not belong to group specified"

  @groups
  Scenario: LDAP user login if it belongs to group
    Given As "sugarAdmin" filling in the following LDAP settings:
      | field | type | value |
      | ldap_hostname | text | 127.0.0.1 |
      | ldap_port | text | 389 |
      | ldap_base_dn | text |  dc=openldap,dc=com |
      | ldap_login_filter | text | |
      | ldap_bind_attr    | text | dn |
      | ldap_login_attr    | text | uid |
      | ldap_group_checkbox    | checkbox | checked |
      | ldap_authentication_checkbox    | checkbox | checked |
      | ldap_admin_user    | text | cn=admin,ou=admins,dc=openldap,dc=com |
      | ldap_admin_password    | text | admin |
      | ldap_auto_create_users | checkbox | checked |
      | ldap_group_dn | text | ou=groups,dc=openldap,dc=com |
      | ldap_group_name | text | cn=Administrators |
      | ldap_group_attr | text | member |
    Given I login as "abey" with password "abey"
    Given I want to delete current user after test
    Then I should see "Setup your user information"
    Then I skip login wizard
    And I logout
