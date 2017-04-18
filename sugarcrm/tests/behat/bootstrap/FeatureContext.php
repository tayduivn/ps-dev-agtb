<?php
namespace Sugarcrm\SugarcrmTests\Bootstrap;

use Behat\MinkExtension\Context\MinkContext;
use Guzzle\Http\Client;

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends MinkContext
{

    /**
     * Click on element found by css selector.
     * @When /^I click "([^"]*)"$/
     * Example: I click "ul.megamenu li button"
     */
    public function iClick($selector)
    {
        $element = $this->getSession()->getPage()->find('css', $selector);
        if (is_null($element)) {
            throw new \RuntimeException("Not found element by selector($selector)");
        }
        $element->click();
    }

    /**
     * Wait until the loading is completed
     * @When /^I wait until the loading is completed$/
     */
    public function iWaitUntilTheLoadingIsCompleted()
    {
        $condition = '!document.querySelector(".alert-wrapper .alert-process .loading") '
            . '&& !document.querySelector("span[sfuuid]:empty:not([class])");';
        $this->getSession()->wait(20000, $condition);
    }

    /**
     * Before working with BWC Iframe need this command.
     * @When /^I switch to BWC$/
     */
    public function switchBwc()
    {
        $this->spin(function (FeatureContext $context) {
            $context->getSession()->getDriver()->switchToIFrame('bwc-frame');
            return boolval($context->getSession()->getPage()->findById('main'));
        });
    }

    /**
     * Output from bwc.
     * @When /^I switch to sidecar$/
     */
    public function switchSidecar()
    {
        $this->getSession()->getDriver()->switchToIFrame(null);
    }

    /**
     * Wait :milliseconds.
     * @When I wait :milliseconds
     */
    public function wait($milliseconds)
    {
        $this->getSession()->wait($milliseconds);
    }

    /**
     * Wait for the page to be redirected to the specific url
     * @Then /^I should be redirected to "([^"]*)"$/
     * @And /^I should be redirected to "([^"]*)"$/
     * Example: I should be redirected to "http://localhost:8000"
     */
    public function iShouldBeRedirectedTo($redirectedTo)
    {
        $this->spin(function (FeatureContext $context) use ($redirectedTo) {
            $diff = $result = array_diff(
                parse_url($redirectedTo),
                parse_url($context->getSession()->getCurrentUrl())
            );
            return empty($diff);
        }, 10);
    }

    /**
     * Wait for the page to be loaded.
     * @And I wait for the page to be loaded
     * @When I wait for the page to be loaded
     */
    public function waitForThePageToBeLoaded()
    {
        $this->getSession()->wait(10000, "document.readyState === 'complete'");
    }

    /**
     * Checking radiobutton.
     * @When /^I check the "([^"]*)" radio button with "([^"]*)" value$/
     * @And /^I check the "([^"]*)" radio button with "([^"]*)" value$/
     * Example: I check the "radioName" radio button with "radioValue" value
     */
    public function iCheckTheRadioButtonWithValue($element, $value)
    {
        $element = $this->fixStepArgument($element);
        $value = $this->fixStepArgument($value);
        $selector = 'input[type="radio"][name="' . $element . '"]';
        foreach ($this->getSession()->getPage()->findAll('css', $selector) as $radio) {
            if ($radio->getAttribute('value') == $value) {
                $radio->check();
                return true;
            }
        }
        return false;
    }

    /**
     * @Then I skip login wizard
     * @And I skip login wizard
     */
    public function iSkipLoginWizard()
    {
        $accessToken = $this->getAccessToken();
        $client = new Client();
        $response = $client->get(
            $this->getMinkParameter('base_url') . '/rest/v10/me/preferences',
            ['OAuth-Token' => $accessToken]
        )->send();
        $userPreferences = json_decode($response->getBody(true));
        $wizard = !(isset($userPreferences->ut) && $userPreferences->ut);
        if ($wizard) {
            $client->put(
                $this->getMinkParameter('base_url') . '/rest/v10/me/preferences',
                ['OAuth-Token' => $accessToken],
                '{"ut":1}'
            )->send();
            $this->getSession()->reload();
            $this->iWaitUntilTheLoadingIsCompleted();
        }
    }

    /**
     * Get access token from localStorage
     *
     * @return string
     */
    protected function getAccessToken()
    {
        return $this->getLocalStorageItem('prod:SugarCRM:AuthAccessToken');
    }

    /**
     * Get local storage item value
     *
     * @param string $key
     *
     * @return string
     */
    protected function getLocalStorageItem($key)
    {
        return json_decode($this->getSession()->getDriver()->evaluateScript("localStorage.getItem('$key')"));
    }

    /**
     * Wait until element is appear on the page
     * @When /^I wait for element "([^"]*)"$/
     * @And /^I wait for element "([^"]*)"$/
     * Example: I wait for element "ul.megamenu li button"
     */
    public function waitForElement($css)
    {
        $css = $this->fixStepArgument($css);
        $this->spin(function (FeatureContext $context) use ($css) {
            $element = $context->getSession()->getPage()->find('css', $css);
            return !is_null($element);
        }, 20);
    }

    /**
     * Spin wrapper for long test.
     * @param \Closure $lambda will call until not return true or time out
     * @param int $wait
     * @return bool
     * @throws \Exception
     */
    public function spin(\Closure $lambda, $wait = 60)
    {
        for ($i = 0; $i < $wait; $i++) {
            try {
                if ($lambda($this)) {
                    return true;
                }
            } catch (\Exception $e) {
                // do nothing
            }
            sleep(1);
        }

        $backtrace = debug_backtrace();

        throw new \Exception(
            "Timeout thrown by " . $backtrace[1]['class'] . "::" . $backtrace[1]['function'] . "()\n" .
            $backtrace[1]['file'] . ", line " . $backtrace[1]['line']
        );
    }
}
