<!---
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
-->

Start out by reading the [Behat Documentation](http://behat.org/en/latest/user_guide.html)

How to create a new feature test suite:

1. create a new feature file <yourFeature>.feature using Gherkin Syntax, see  if it doesn't already exist in the /features directory.
2. If a context doesn't already exist for your feature, create one in tests/behat/Contexts
    * Your Context should implement Behat\Behat\Context\Context
    * See [Testing Features](http://behat.org/en/latest/user_guide/context.html) for details
3. edit behat.yml as following:
    * edit suites section by adding your test suite:
        ```yaml
        <yourFeature>:
          paths:
            - %paths.base%/../../features
          contexts:
            - Sugarcrm\SugarcrmTestsBehat\Contexts\SugarTest
            - Sugarcrm\SugarcrmTestsBehat\Contexts\<yourFeature>
          filters:
            tags: "@<yourFeature>"
        ```
4. From `tests/behat`, run `../../vendor/bin/behat --tags "@<yourFeature>"`
