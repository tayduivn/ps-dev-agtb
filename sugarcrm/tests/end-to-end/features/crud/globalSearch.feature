@pr @stress-test @xxx
Feature: Global Search

  Background:
    Given I use default account
    Given I launch App

    Given Accounts records exist:
      | *name               | tag                               | email                           |
      | ANewAccount_By_GL03 | accountTag,sharedTag,tagByNumber3 | alias.jim@example.com (primary) |
      | ANewAccount_By_GL02 | accountTag,sharedTag,tagByNumber2 | johnnie@gmail.com (primary)     |
      | ANewAccount_By_GL01 | accountTag,SharedTag,tagByNumber1 | abc@cnn.com (primary)           |

    Given Contacts records exist:
      | *first_name         | last_name | tag                               | email                                                              |
      | New_Contact03_By_GL | Lando     | contactTag,sharedTag,tagByNumber3 | hr.info.hr@example.com (primary),esther_super_lady@ventureland.com |
      | New_Contact02_By_GL | Lando     | contactTag,sharedTag,tagByNumber2 | im.im@example.name (primary),jelle_vink_architect@gmail.com        |
      | New_Contact01_By_GL | Lando     | contactTag,sharedTag,tagByNumber1 | david_wheeler@gmail.com (primary),info98@example.com.uk            |


  Scenario: Global Search Test

    Given I open search view and login
    Then I should be able to perform global search against each test case from the following table
      | testCaseTitle                                      | modulesToSearch   | tagsToSearch                      | filtersToSearch                                         | textToSearch                         | expectedNumOfMatch | expectedHighlightedItemsList                                                 |
      | fts01_allMods_1Tag_2filters_plainText              | all               | tagByNumber2                      | Created by Me (1),Modified by Me (1),Assigned to Me (1) | johnn                                | 1                  | johnnie@gmail.com                                                            |
      | fts02_AccCont_3Tags_3Filters_lgcOperators          | Accounts,Contacts | tagByNumber3,contactTag,sharedTag | Created by Me (1),Modified by Me (1),Contacts (1)       | ( johnn OR esther_su ) NOT gmail     | 1                  |                                                                              |
      | fts03_allMods_noTags_noFilters_moreThan15CharsLong | all               |                                   |                                                         | esther_super_lady@ventur             | 1                  | esther_super_lady@ventureland.com                                            |
      | fts04_irrelevantLeads_1Tag                         | Leads             | sharedTag                         |                                                         | johnn                                | 0                  |                                                                              |
      | fts05_Contacts_noTag_noFilter_lgcOperators         | Contacts          |                                   |                                                         | ( johnn \| esther_su ) - gmail       | 0                  |                                                                              |
      | fts06_allMods_noTag_noFilter_lgcOperators          | all               |                                   |                                                         | (new & by & com) - contact NOT gmail | 1                  | New_Contact03_By_GL,hr.info.hr@example.com,esther_super_lady@ventureland.com |
